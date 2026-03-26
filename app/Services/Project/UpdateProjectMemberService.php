<?php

namespace App\Services\Project;


namespace App\Services\Project;

use App\Actions\App\Project\CleanupOrphanedRolesAction;
use App\Actions\App\Project\UpdateMemberRoleAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
use App\Models\ProjectRolePermission;
use App\Models\ProjectUser;

class UpdateProjectMemberService
{
    public function __construct(
        private UpdateMemberRoleAction $updateMemberRole,
        private CleanupOrphanedRolesAction $cleanupOrphanedRolesAction,
    ) {}

    /**
     * Codes válidos para roles de proyecto
     */
    private const VALID_ROLE_CODES = [
        'ADM', // Administrator
        'LDR', // Leader
        'DEV', // Developer
        'TST', // Tester
        'DOC'  // Documenter
    ];

    public function updateRole(Project $project, int $userId, string $newRoleCode): ProjectUser
    {
        $this->validateProject($project);
        $this->validateAdminPermissions($project);

        // Validar que el code es válido
        $this->validateRoleCode($newRoleCode);

        $permissionScheme = $this->validateRoleExistsInCatalog($newRoleCode);

        $assignment = $this->getAssignment($project, $userId);

        $this->validateNotSameRole($assignment, $newRoleCode);

        $this->validateNotLastAdminChange($project, $assignment, $newRoleCode);

        $this->validateNotSelf($assignment);

        $oldProjectRole = $assignment->role;

        $projectRole = $this->getOrCreateProjectRole($project, $newRoleCode, $permissionScheme);

        return \DB::transaction(function () use ($assignment, $projectRole, $oldProjectRole) {
            $assignment->lockForUpdate();

            $updatedAssignment = $this->updateMemberRole->execute(
                $assignment->id,
                $projectRole->id
            );

            $this->cleanupOrphanedRolesAction->execute($oldProjectRole);

            return $updatedAssignment->load([
                'user',
                'role.permissionScheme.scheme.permissions'
            ]);
        }, 5);
    }

    /**
     * Validar que el code del rol es válido
     */
    private function validateRoleCode(string $roleCode): void
    {
        if (!in_array($roleCode, self::VALID_ROLE_CODES)) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Code de rol no válido',
                    'reason' => 'El code especificado no es válido para roles de proyecto',
                    'requested_code' => $roleCode,
                    'valid_codes' => self::VALID_ROLE_CODES
                ]),
                400
            );
        }
    }

    /**
     * Validar que el rol existe en el catálogo global (ProjectPermissionScheme)
     * Ahora busca por code en lugar de name
     */
    private function validateRoleExistsInCatalog(string $roleCode): ProjectPermissionScheme
    {
        $permissionScheme = ProjectPermissionScheme::where('code', $roleCode)->first();

        if (!$permissionScheme) {
            $availableRoles = ProjectPermissionScheme::pluck('code')->toArray();

            throw new ProjectException(
                json_encode([
                    'error' => 'Rol no válido',
                    'reason' => 'El tipo de rol especificado no existe en el catálogo de roles',
                    'requested_role' => $roleCode,
                    'available_roles' => $availableRoles
                ]),
                400
            );
        }

        return $permissionScheme;
    }

    /**
     * Obtener o crear el ProjectRole para este proyecto
     * Ahora usa code en lugar de type
     */
    private function getOrCreateProjectRole(Project $project, string $roleCode, ProjectPermissionScheme $permissionScheme): ProjectRole
    {
        return \DB::transaction(function () use ($project, $roleCode, $permissionScheme) {
            $projectRole = $project->roles()
                ->where('code', $roleCode)
                ->lockForUpdate()
                ->first();

            if (!$projectRole) {
                $projectRole = ProjectRole::create([
                    'project_id' => $project->id,
                    'code' => $roleCode,
                    'type' => $this->getRoleTypeFromCode($roleCode) // Mantener type para compatibilidad
                ]);

                ProjectRolePermission::create([
                    'project_role_id' => $projectRole->id,
                    'permission_scheme_id' => $permissionScheme->id
                ]);
            }

            return $projectRole;
        });
    }

    /**
     * Obtener el tipo de rol legible desde el code
     */
    private function getRoleTypeFromCode(string $code): string
    {
        return match($code) {
            'ADM' => 'administrator',
            'LDR' => 'leader',
            'DEV' => 'developer',
            'TST' => 'tester',
            'DOC' => 'documenter',
            default => 'member'
        };
    }

    /**
     * Validar que el proyecto existe y no está eliminado
     */
    private function validateProject(Project $project): void
    {
        if ($project->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto no disponible',
                    'reason' => 'No se pueden cambiar roles en un proyecto eliminado',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString(),
                ]),
                400
            );
        }
    }

    /**
     * Validar que el usuario actual es administrador
     * Ahora busca por code 'ADM' en lugar de type 'administrators'
     */
    private function validateAdminPermissions(Project $project): void
    {
        $userId = auth()->id();

        if (! $userId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no autenticado',
                    'reason' => 'Se requiere un usuario autenticado para esta acción',
                ]),
                401
            );
        }

        $isAdmin = $project->roles()
            ->where('code', 'ADM')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $isAdmin) {
            $userRole = $project->roles()
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->first();

            $roleCode = $userRole?->code ?? 'Sin rol asignado';

            throw new ProjectException(
                json_encode([
                    'error' => 'Permiso denegado',
                    'reason' => 'Solo los administradores pueden cambiar roles',
                    'user_id' => $userId,
                    'user_role' => $roleCode,
                    'project_id' => $project->id,
                    'required_role' => 'ADM'
                ]),
                403
            );
        }
    }

    /**
     * Obtener la asignación y verificar que pertenece al proyecto
     */
    private function getAssignment(Project $project, int $userId): ProjectUser
    {
        $assignment = ProjectUser::with(['role', 'user'])
            ->where('user_id', $userId)
            ->whereHas('role', fn($q) => $q->where('project_id', $project->id))
            ->whereNull('deleted_at')
            ->first();

        if (! $assignment) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Miembro no encontrado',
                    'reason' => 'El miembro no existe en este proyecto',
                    'user_id' => $userId,
                    'project_id' => $project->id,
                ]),
                404
            );
        }

        return $assignment;
    }

    /**
     * Validar que no sea el mismo rol (comparando codes, no types)
     */
    private function validateNotSameRole(ProjectUser $assignment, string $newRoleCode): void
    {
        if ($assignment->role->code === $newRoleCode) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Mismo rol',
                    'reason' => 'El miembro ya tiene este rol asignado',
                    'user_id' => $assignment->user_id,
                    'current_role' => $assignment->role->code,
                    'attempted_role' => $newRoleCode
                ]),
                400
            );
        }
    }

    /**
     * Validar cambio de último administrador
     * Ahora usa code 'ADM' en lugar de type 'administrators'
     */
    private function validateNotLastAdminChange(Project $project, ProjectUser $assignment, string $newRoleCode): void
    {
        // Si el usuario no es admin actualmente, no hay problema
        if ($assignment->role->code !== 'ADM') {
            return;
        }

        // Si el nuevo rol también es admin, no hay problema
        if ($newRoleCode === 'ADM') {
            return;
        }

        // Contar administradores actuales (por code 'ADM')
        $adminCount = $project->roles()
            ->where('code', 'ADM')
            ->withCount('users')
            ->get()
            ->sum('users_count');

        // Si es el último admin, no permitir cambiar a otro rol
        if ($adminCount === 1) {
            // Buscar otros miembros que podrían ser administradores
            $candidates = ProjectUser::whereHas('role', function ($q) use ($project) {
                $q->where('project_id', $project->id)
                    ->where('code', '!=', 'ADM');
            })
                ->with(['user', 'role'])
                ->get()
                ->map(fn($member) => [
                    'id' => $member->user_id,
                    'name' => $member->user?->name,
                    'email' => $member->user?->email,
                    'current_role' => $member->role->code,
                    'assignment_id' => $member->id
                ])
                ->values()
                ->toArray();

            throw new ProjectException(
                json_encode([
                    'error' => 'Último administrador',
                    'reason' => 'No puedes cambiar el rol del último administrador',
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'user' => [
                        'id' => $assignment->user_id,
                        'name' => $assignment->user?->name,
                        'email' => $assignment->user?->email,
                    ],
                    'current_role' => 'ADM',
                    'attempted_role' => $newRoleCode,
                    'admin_count' => $adminCount,
                    'suggestion' => 'Antes de cambiar, promueve a otro usuario a Administrador (ADM)',
                    'candidates' => $candidates,
                ]),
                400
            );
        }
    }

    /**
     * Validar que no se cambie el rol a sí mismo
     */
    private function validateNotSelf(ProjectUser $assignment): void
    {
        $currentUserId = auth()->id();

        if ($assignment->user_id === $currentUserId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Auto-cambio no permitido',
                    'reason' => 'No puedes cambiar tu propio rol',
                    'suggestion' => 'Pide a otro administrador que cambie tu rol',
                ]),
                400
            );
        }
    }
}