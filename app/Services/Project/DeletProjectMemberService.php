<?php

namespace App\Services\Project;

use App\Actions\App\Project\RemoveProjectMemberAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\DB;

class DeletProjectMemberService
{
    public function __construct(
        private RemoveProjectMemberAction $removeMember
    ) {}

    /**
     * Eliminar un miembro del proyecto
     */
    public function removeMember(Project $project, int $assignmentId): array
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validateAdminPermissions($project);

        // Obtener la asignación
        $assignment = $this->getAssignment($project, $assignmentId);

        // Validaciones específicas
        $this->validateNotLastAdmin($project, $assignment);
        $this->validateNotSelf($assignment);

        // TRANSACCIÓN
        return DB::transaction(function () use ($assignment, $project) {

            // Guardar datos antes de eliminar para la respuesta
            $memberData = [
                'assignment_id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user?->name,
                'user_email' => $assignment->user?->email,
                'role_id' => $assignment->role?->id,
                'role_type' => $assignment->role?->type,
                'assigned_at' => $assignment->created_at->toDateTimeString()
            ];

            // Eliminar miembro
            $this->removeMember->execute($assignment->id);

            return [
                'removed_member' => $memberData,
                'project' => $project,
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
                    'reason' => 'No se pueden eliminar miembros de un proyecto eliminado',
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
                    'reason' => 'Solo los administradores pueden eliminar miembros',
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
        $assignment = ProjectUser::with(['user', 'role'])
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
                    'error' => 'Miembro ya eliminado',
                    'reason' => 'Este miembro ya ha sido eliminado del proyecto',
                    'assignment_id' => $assignmentId,
                    'deleted_at' => $assignment->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }

        return $assignment;
    }

    /**
     * Validar que no se elimine al último administrador
     */
    private function validateNotLastAdmin(Project $project, ProjectUser $assignment): void
    {
        // Si el usuario a eliminar no es admin, no hay problema
        if ($assignment->role->type !== 'administrators') {
            return;
        }

        // Contar administradores actuales
        $adminCount = $project->roles()
            ->where('type', 'administrators')
            ->withCount('users')
            ->get()
            ->sum('users_count');

        // Si es el último admin, no permitir
        if ($adminCount === 1) {
            // Buscar candidatos para sugerir
            $candidates = $project->roles()
                ->where('type', '!=', 'administrators')
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
                    'reason' => 'No puedes eliminar al último administrador del proyecto',
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'user_to_remove' => [
                        'id' => $assignment->user_id,
                        'name' => $assignment->user?->name,
                        'email' => $assignment->user?->email
                    ],
                    'admin_count' => $adminCount,
                    'suggestion' => 'Antes de eliminar, promueve a otro usuario a Administrador',
                    'candidates' => $candidates
                ]),
                400
            );
        }
    }

    /**
     * Validar que no se elimine a sí mismo (opcional)
     */
    private function validateNotSelf(ProjectUser $assignment): void
    {
        $currentUserId = auth()->id();

        if ($assignment->user_id === $currentUserId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Auto-eliminación no permitida',
                    'reason' => 'No puedes eliminarte a ti mismo del proyecto',
                    'suggestion' => 'Pide a otro administrador que te elimine'
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