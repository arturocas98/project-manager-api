<?php

namespace App\Services\Project;

use App\Actions\App\Project\RemoveProjectMemberAction;
use App\Exceptions\ProjectException;
use App\Models\Incidence;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\DB;

class DeletProjectMemberService
{
    public function __construct(
        private RemoveProjectMemberAction $removeMember
    ) {}

    /**
     * Eliminar un miembro del proyecto usando su user_id
     */
    public function removeMember(Project $project, int $userId): array
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validateAdminPermissions($project);

        // Obtener la asignación del usuario en el proyecto
        $assignment = $this->getUserAssignment($project, $userId);

        // Validaciones específicas
        $this->validateNotLastAdmin($project, $assignment);
        $this->validateNotSelf($assignment);

        // TRANSACCIÓN
        return DB::transaction(function () use ($assignment, $project, $userId) {

            // Guardar datos antes de eliminar para la respuesta
            $memberData = [
                'assignment_id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user?->name,
                'user_email' => $assignment->user?->email,
                'role_id' => $assignment->role?->id,
                'role_type' => $assignment->role?->type,
                'assigned_at' => $assignment->created_at->toDateTimeString(),
            ];

            // 1. Desasignar incidencias del usuario en este proyecto
            $unassignedIncidences = $this->unassignUserIncidences($project, $userId);

            // 2. Verificar si hay otros usuarios con el mismo rol
            $otherUsersWithSameRole = ProjectUser::where('project_role_id', $assignment->project_role_id)
                ->where('id', '!=', $assignment->id)
                ->whereNull('deleted_at')
                ->exists();

            // 3. Eliminar el registro del usuario
            $assignment->delete();

            // 4. Si no hay más usuarios con este rol, eliminar también el rol
            if (!$otherUsersWithSameRole) {
                $assignment->role->delete();
            }

            return [
                'removed_member' => $memberData,
                'project' => $project,
                'unassigned_incidences' => $unassignedIncidences,
                'role_deleted' => !$otherUsersWithSameRole,
                'timestamp' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Desasignar al usuario de todas las incidencias del proyecto
     */
    private function unassignUserIncidences(Project $project, int $userId): array
    {
        // Buscar todas las incidencias del proyecto asignadas a este usuario
        $incidences = Incidence::where('project_id', $project->id)
            ->where('assigned_user_id', $userId)
            ->get();

        $incidenceData = $incidences->map(fn($incidence) => [
            'id' => $incidence->id,
            'title' => $incidence->title,
            'state' => $incidence->incidenceState?->name,
            'priority' => $incidence->incidencePriority?->name,
            'previous_assigned_user_id' => $userId,
        ])->toArray();

        // Actualizar las incidencias: asignado_user_id = null
        Incidence::where('project_id', $project->id)
            ->where('assigned_user_id', $userId)
            ->update(['assigned_user_id' => null]);

        return [
            'count' => $incidences->count(),
            'incidences' => $incidenceData,
        ];
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
                    'deleted_at' => $project->deleted_at?->toDateTimeString(),
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
            ->where('type', 'administrators')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $isAdmin) {
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
     * Obtener la asignación del usuario en el proyecto
     */
    private function getUserAssignment(Project $project, int $userId): ProjectUser
    {
        $assignment = ProjectUser::with(['user', 'role'])
            ->where('user_id', $userId)
            ->whereHas('role', fn($q) => $q->where('project_id', $project->id))
            ->first();

        if (! $assignment) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Miembro no encontrado',
                    'reason' => 'El usuario no es miembro de este proyecto',
                    'user_id' => $userId,
                    'project_id' => $project->id,
                ]),
                404
            );
        }

        if ($assignment->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Miembro ya eliminado',
                    'reason' => 'Este usuario ya ha sido eliminado del proyecto',
                    'user_id' => $userId,
                    'deleted_at' => $assignment->deleted_at?->toDateTimeString(),
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

        // Contar administradores actuales (solo los no eliminados)
        $adminCount = ProjectUser::whereHas('role', function($q) use ($project) {
            $q->where('project_id', $project->id)
                ->where('type', 'administrators');
        })
            ->whereNull('deleted_at')
            ->count();

        // Si es el último admin, no permitir
        if ($adminCount === 1) {
            // Buscar candidatos para sugerir
            $candidates = ProjectUser::whereHas('role', function($q) use ($project) {
                $q->where('project_id', $project->id)
                    ->where('type', '!=', 'administrators');
            })
                ->with(['user', 'role'])
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($pu) => [
                    'id' => $pu->user->id,
                    'name' => $pu->user->name,
                    'email' => $pu->user->email,
                    'current_role' => $pu->role->type,
                ])
                ->unique('id')
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
                        'email' => $assignment->user?->email,
                    ],
                    'admin_count' => $adminCount,
                    'suggestion' => 'Antes de eliminar, promueve a otro usuario a Administrador',
                    'candidates' => $candidates,
                ]),
                400
            );
        }
    }

    /**
     * Validar que no se elimine a sí mismo
     */
    private function validateNotSelf(ProjectUser $assignment): void
    {
        $currentUserId = auth()->id();

        if ($assignment->user_id === $currentUserId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Auto-eliminación no permitida',
                    'reason' => 'No puedes eliminarte a ti mismo del proyecto',
                    'suggestion' => 'Pide a otro administrador que te elimine',
                ]),
                400
            );
        }
    }
}