<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberRemovedResource extends JsonResource
{
    /**
     * El resource recibe el array devuelto por DeletProjectMemberService
     * [
     *   'removed_member' => array, // Datos planos del miembro eliminado
     *   'project' => Project,       // Modelo del proyecto
     *   'timestamp' => string
     * ]
     */
    public function toArray($request)
    {
        // Extraer datos del resultado del service
        $removedMember = $this->resource['removed_member'] ?? null;
        $project = $this->resource['project'] ?? null;

        // Validación básica
        if (!$project || !$removedMember) {
            return $this->errorResponse('Datos insuficientes para procesar la respuesta');
        }

        // Calcular miembros restantes
        $remainingMembers = $this->getRemainingMembersCount($project);

        return [
            'data' => [
                'message' => 'Miembro eliminado del proyecto exitosamente',
                'removed_member' => [
                    'assignment_id' => $removedMember['assignment_id'],
                    'user' => [
                        'id' => $removedMember['user_id'],
                        'name' => $removedMember['user_name'],
                        'email' => $removedMember['user_email'],
                    ],
                    'role' => [
                        'id' => $removedMember['role_id'],
                        'type' => $removedMember['role_type'],
                    ],
                    'assigned_at' => $removedMember['assigned_at'],
                    'removed_at' => $this->resource['timestamp'] ?? now()->toDateTimeString()
                ],
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'key' => $project->key,
                    'links' => [
                        'self' => route('projects.show', ['project' => $project->id]),
                    ],
                ],
                'stats' => [
                    'remaining_members' => $remainingMembers,
                    'roles_breakdown' => $this->getRolesBreakdown($project),
                ],
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member_removal',
                'action' => 'remove_member',
                'status' => 'success',
                'project_id' => $project->id,
            ],
            'links' => [
                'self' => route('projects.members.index', ['project' => $project->id]),
                'parent' => route('projects.show', ['project' => $project->id]),
                'project' => route('projects.show', ['project' => $project->id]),
                'members' => route('projects.members.index', ['project' => $project->id]),
                'add_member' => route('projects.members.store', ['project' => $project->id]),
            ],
        ];
    }

    /**
     * Obtener el número total de miembros restantes en el proyecto
     */
    private function getRemainingMembersCount($project): int
    {
        return $project->roles()
            ->withCount('users')
            ->get()
            ->sum('users_count');
    }

    /**
     * Obtener desglose de miembros por rol después de la eliminación
     */
    private function getRolesBreakdown($project): array
    {
        $roles = $project->roles()->withCount('users')->get();

        return $roles->map(function ($role) {
            return [
                'role_id' => $role->id,
                'role_type' => $role->type,
                'members_count' => $role->users_count,
            ];
        })->values()->toArray();
    }

    /**
     * Respuesta de error en caso de datos inválidos
     */
    private function errorResponse(string $message): array
    {
        return [
            'data' => [
                'message' => $message,
                'removed_member' => null,
                'project' => null,
                'stats' => null
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member_removal',
                'action' => 'remove_member',
                'status' => 'error'
            ],
            'links' => []
        ];
    }
}
