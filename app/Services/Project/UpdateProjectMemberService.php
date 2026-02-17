<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectRole;
use App\Exceptions\ProjectException;
use Illuminate\Support\Facades\DB;
use App\Actions\App\Project\UpdateMemberRoleAction;

class UpdateProjectMemberService
{
    public function __construct(
        private UpdateMemberRoleAction $updateMemberRole
    ) {}

    /**
     * Cambiar el rol de un miembro del proyecto
     */
    public function updateRole(Project $project, int $assignmentId, string $newRoleType): array
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validateAdminPermissions($project);

        // Obtener la asignación actual
        $assignment = $this->getAssignment($project, $assignmentId);

        // Obtener el nuevo rol
        $newRole = $this->getNewRole($project, $newRoleType);

        // Validaciones específicas
        $this->validateNotSameRole($assignment, $newRole);
        $this->validateNotLastAdminChange($project, $assignment, $newRole);
        $this->validateNotSelf($assignment);

        // TRANSACCIÓN
        return DB::transaction(function () use ($assignment, $newRole, $project) {

            // Guardar datos antes del cambio
            $oldRole = [
                'id' => $assignment->role->id,
                'type' => $assignment->role->type
            ];

            // Cambiar rol
            $updatedAssignment = $this->updateMemberRole->execute(
                $assignment->id,
                $newRole->id
            );

            return [
                'assignment' => $updatedAssignment,
                'project' => $project,
                'changes' => [
                    'from' => $oldRole,
                    'to' => [
                        'id' => $newRole->id,
                        'type' => $newRole->type
                    ]
                ],
                'user' => $updatedAssignment->user,
                'timestamp' => now()->toDateTimeString()
            ];
        });
    }

    /**
     * Validar que el proyecto existe
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
            ->where('type', 'Administrators')
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
                    'required_role' => 'Administrators'
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

        if ($assignment->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Miembro eliminado',
                    'reason' => 'No se puede cambiar el rol de un miembro eliminado',
                    'assignment_id' => $assignmentId,
                    'deleted_at' => $assignment->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }

        return $assignment;
    }

    /**
     * Obtener el nuevo rol
     */
    private function getNewRole(Project $project, string $roleType): ProjectRole
    {
        $role = $project->roles()
            ->where('type', $roleType)
            ->first();

        if (!$role) {
            $availableRoles = $project->roles()->pluck('type')->toArray();

            throw new ProjectException(
                json_encode([
                    'error' => 'Rol no disponible',
                    'reason' => 'El rol especificado no existe en este proyecto',
                    'requested_role' => $roleType,
                    'project_id' => $project->id,
                    'available_roles' => $availableRoles
                ]),
                400
            );
        }

        return $role;
    }

    /**
     * Validar que no sea el mismo rol
     */
    private function validateNotSameRole(ProjectUser $assignment, ProjectRole $newRole): void
    {
        if ($assignment->role->id === $newRole->id) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Mismo rol',
                    'reason' => 'El miembro ya tiene este rol asignado',
                    'user_id' => $assignment->user_id,
                    'current_role' => $assignment->role->type,
                    'attempted_role' => $newRole->type
                ]),
                400
            );
        }
    }

    /**
     * Validar cambio de último administrador
     */
    private function validateNotLastAdminChange(Project $project, ProjectUser $assignment, ProjectRole $newRole): void
    {
        // Si el usuario no es admin actualmente, no hay problema
        if ($assignment->role->type !== 'Administrators') {
            return;
        }

        // Si el nuevo rol también es admin, no hay problema
        if ($newRole->type === 'Administrators') {
            return;
        }

        // Contar administradores actuales
        $adminCount = $project->roles()
            ->where('type', 'Administrators')
            ->withCount('users')
            ->get()
            ->sum('users_count');

        // Si es el último admin, no permitir cambiar a otro rol
        if ($adminCount === 1) {
            // Buscar otros admins potenciales
            $candidates = $project->roles()
                ->where('type', '!=', 'Administrators')
                ->with('users')
                ->get()
                ->pluck('users')
                ->flatten()
                ->unique('id')
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'current_role' => $this->getUserRoleInProject($project, $user->id)
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
                    'current_role' => 'Administrators',
                    'attempted_role' => $newRole->type,
                    'admin_count' => $adminCount,
                    'suggestion' => 'Antes de cambiar, promueve a otro usuario a Administrador',
                    'candidates' => $candidates
                ]),
                400
            );
        }
    }

    /**
     * Validar que no se cambie el rol a sí mismo (opcional)
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

    /**
     * Obtener rol de un usuario en el proyecto
     */
    private function getUserRoleInProject(Project $project, int $userId): ?string
    {
        $role = $project->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->first();

        return $role?->type;
    }
}