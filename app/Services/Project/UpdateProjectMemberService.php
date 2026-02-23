<?php

namespace App\Services\Project;


namespace App\Services\Project;

use App\Actions\App\Project\UpdateMemberRoleAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
use App\Models\ProjectUser;
class UpdateProjectMemberService
{
    public function __construct(
        private UpdateMemberRoleAction $updateMemberRole
    ) {}

    /**
     * Cambiar el rol de un miembro del proyecto
     */
    public function updateRole(Project $project, int $assignmentId, string $newRoleType): ProjectUser
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validateAdminPermissions($project);

        // 1️⃣ Validar que el nuevo rol existe en el catálogo global
        $permissionScheme = $this->validateRoleExistsInCatalog($newRoleType);

        // 2️⃣ Obtener la asignación actual (ProjectUser)
        $assignment = $this->getAssignment($project, $assignmentId);

        // 3️⃣ Validar que no sea el mismo rol
        $this->validateNotSameRole($assignment, $newRoleType);

        // 4️⃣ Validar que no sea el último administrador
        $this->validateNotLastAdminChange($project, $assignment, $newRoleType);

        // 5️⃣ Validar que no se cambie a sí mismo
        $this->validateNotSelf($assignment);

        // 6️⃣ Obtener o crear el ProjectRole para este proyecto con el nuevo tipo
        $projectRole = $this->getOrCreateProjectRole($project, $newRoleType, $permissionScheme);

        // TRANSACCIÓN
        return \DB::transaction(function () use ($assignment, $projectRole) {
            // Cambiar rol actualizando project_role_id en ProjectUser
            $updatedAssignment = $this->updateMemberRole->execute(
                $assignment->id,
                $projectRole->id
            );

            return $updatedAssignment->load(['user', 'role.permissionScheme.scheme.permissions']);
        });
    }

    /**
     * Validar que el rol existe en el catálogo global (ProjectPermissionScheme)
     */
    private function validateRoleExistsInCatalog(string $roleType): ProjectPermissionScheme
    {
        $permissionScheme = ProjectPermissionScheme::where('name', $roleType)->first();

        if (!$permissionScheme) {
            $availableRoles = ProjectPermissionScheme::pluck('name')->toArray();

            throw new ProjectException(
                json_encode([
                    'error' => 'Rol no válido',
                    'reason' => 'El tipo de rol especificado no existe en el catálogo de roles',
                    'requested_role' => $roleType,
                    'available_roles' => $availableRoles
                ]),
                400
            );
        }

        return $permissionScheme;
    }

    /**
     * Obtener o crear el ProjectRole para este proyecto
     */
    private function getOrCreateProjectRole(Project $project, string $roleType, ProjectPermissionScheme $permissionScheme): ProjectRole
    {
        // Buscar si ya existe este rol en el proyecto
        $projectRole = $project->roles()
            ->where('type', $roleType)
            ->first();

        // Si no existe, crearlo con el esquema de permisos correspondiente
        if (!$projectRole) {
            $projectRole = \DB::transaction(function () use ($project, $roleType, $permissionScheme) {
                // Crear el rol del proyecto
                $newRole = ProjectRole::create([
                    'project_id' => $project->id,
                    'type' => $roleType
                ]);

                // Asignarle el esquema de permisos
                // Asumiendo que tienes una tabla project_role_permissions que relaciona roles con esquemas
                $newRole->permissionScheme()->create([
                    'permission_scheme_id' => $permissionScheme->id
                ]);

                return $newRole;
            });
        }

        return $projectRole;
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
                    'deleted_at' => $project->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }
    }

    /**
     * Validar que el usuario actual es administrador
     */
    private function validateAdminPermissions(Project $project): void
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no autenticado',
                    'reason' => 'Se requiere un usuario autenticado para esta acción'
                ]),
                401
            );
        }

        $isAdmin = $project->roles()
            ->where('type', 'administrators')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (!$isAdmin) {
            $userRole = $project->roles()
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->first();

            $roleName = $userRole?->type ?? 'Sin rol asignado';

            throw new ProjectException(
                json_encode([
                    'error' => 'Permiso denegado',
                    'reason' => 'Solo los administradores pueden cambiar roles',
                    'user_id' => $userId,
                    'user_role' => $roleName,
                    'project_id' => $project->id,
                    'required_role' => 'administrators'
                ]),
                403
            );
        }
    }

    /**
     * Obtener la asignación y verificar que pertenece al proyecto
     */
    private function getAssignment(Project $project, int $assignmentId): ProjectUser
    {
        $assignment = ProjectUser::with(['role', 'user'])
            ->where('id', $assignmentId)
            ->whereHas('role', fn($q) => $q->where('project_id', $project->id))
            ->whereNull('deleted_at')
            ->first();

        if (!$assignment) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Miembro no encontrado',
                    'reason' => 'El miembro no existe en este proyecto',
                    'assignment_id' => $assignmentId,
                    'project_id' => $project->id
                ]),
                404
            );
        }

        return $assignment;
    }

    /**
     * Validar que no sea el mismo rol (comparando tipos, no IDs)
     */
    private function validateNotSameRole(ProjectUser $assignment, string $newRoleType): void
    {
        if ($assignment->role->type === $newRoleType) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Mismo rol',
                    'reason' => 'El miembro ya tiene este rol asignado',
                    'user_id' => $assignment->user_id,
                    'current_role' => $assignment->role->type,
                    'attempted_role' => $newRoleType
                ]),
                400
            );
        }
    }

    /**
     * Validar cambio de último administrador
     */
    private function validateNotLastAdminChange(Project $project, ProjectUser $assignment, string $newRoleType): void
    {
        // Si el usuario no es admin actualmente, no hay problema
        if ($assignment->role->type !== 'administrators') {
            return;
        }

        // Si el nuevo rol también es admin, no hay problema
        if ($newRoleType === 'administrators') {
            return;
        }

        // Contar administradores actuales
        $adminCount = $project->roles()
            ->where('type', 'administrators')
            ->withCount('users')
            ->get()
            ->sum('users_count');

        // Si es el último admin, no permitir cambiar a otro rol
        if ($adminCount === 1) {
            // Buscar otros miembros que podrían ser administradores
            $candidates = ProjectUser::whereHas('role', function($q) use ($project) {
                $q->where('project_id', $project->id)
                    ->where('type', '!=', 'administrators');
            })
                ->with(['user', 'role'])
                ->get()
                ->map(fn($member) => [
                    'id' => $member->user_id,
                    'name' => $member->user?->name,
                    'email' => $member->user?->email,
                    'current_role' => $member->role->type,
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
                        'email' => $assignment->user?->email
                    ],
                    'current_role' => 'administrators',
                    'attempted_role' => $newRoleType,
                    'admin_count' => $adminCount,
                    'suggestion' => 'Antes de cambiar, promueve a otro usuario a Administrador',
                    'candidates' => $candidates
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
                    'suggestion' => 'Pide a otro administrador que cambie tu rol'
                ]),
                400
            );
        }
    }
}