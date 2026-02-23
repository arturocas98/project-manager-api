<?php

namespace App\Services\Project;

use App\Actions\App\Project\AssignUserToRoleAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\DB;

class ProjectMemberService
{
    public function __construct(
        private AssignUserToRoleAction $assignUserToRole,
    ) {}


    public function addMember(Project $project, int $userId, string $roleType): ProjectUser
    {
        // VALIDACIONES (igual)
        $this->validateProject($project);
        $this->validateAdminPermissions($project);
        $this->validateUserNotInProject($project, $userId);
        $projectRole = $this->getProjectRole($project, $roleType);

        // TRANSACCIÓN - Retorna SOLO el modelo
        return DB::transaction(function () use ($projectRole, $userId) {
            $assignment = $this->assignUserToRole->execute($projectRole->id, $userId);
            return $assignment->load(['role', 'user']);
        });
    }

    private function validateProject(Project $project): void
    {
        if ($project->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto no disponible',
                    'reason' => 'No se pueden añadir miembros a un proyecto eliminado',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }
    }

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
                    'reason' => 'Solo los administradores pueden añadir miembros',
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
     * ✅ Validar que el usuario NO tiene NINGÚN rol en el proyecto
     * (MIRANDO DIRECTAMENTE EN PROJECT_USER)
     */
    private function validateUserNotInProject(Project $project, int $userId): void
    {
        // Buscar si el usuario tiene ALGÚN project_role asociado a este proyecto
        $existingAssignment = ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        if ($existingAssignment) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario ya en el proyecto',
                    'reason' => 'El usuario ya es miembro del proyecto',
                    'user_id' => $userId,
                    'project_id' => $project->id,
                    'current_role' => [
                        'id' => $existingAssignment->project_role_id,
                        'type' => $existingAssignment->role->type,
                        'assignment_id' => $existingAssignment->id
                    ],
                    'suggestion' => 'Solo se permite un rol por usuario en el proyecto'
                ]),
                400
            );
        }
    }

    /**
     * ✅ Obtener el rol específico del proyecto por su tipo
     * (ASUMIMOS que solo hay UNO por tipo)
     */
    /**
     * ✅ Obtener el rol específico del proyecto por su tipo
     * Si no existe, lo crea automáticamente
     */
    private function getProjectRole(Project $project, string $roleType): ProjectRole
    {
        // Buscar si existe el rol en el proyecto
        $role = $project->roles()
            ->where('type', $roleType)
            ->first();

        // Si no existe, lo creamos
        if (!$role) {
            $role = ProjectRole::create([
                'project_id' => $project->id,
                'type' => $roleType
            ]);

            // Opcional: Log o evento para tracking
            \Log::info('Project role created automatically', [
                'project_id' => $project->id,
                'role_type' => $roleType,
                'created_by' => auth()->id()
            ]);
        }

        return $role;
    }
}