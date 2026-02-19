<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberRemovedResource extends JsonResource
{
    public function toArray($request)
    {
        $project = $this['project'];
        $remainingMembers = $project->roles()
            ->withCount('users')
            ->get()
            ->sum('users_count');

        return [
            'data' => [
                'message' => 'Miembro eliminado del proyecto exitosamente',
                'removed_member' => [
                    'assignment_id' => $this['removed_member']['assignment_id'],
                    'user' => [
                        'id' => $this['removed_member']['user_id'],
                        'name' => $this['removed_member']['user_name'],
                        'email' => $this['removed_member']['user_email'],
                    ],
                    'role' => [
                        'id' => $this['removed_member']['role_id'],
                        'type' => $this['removed_member']['role_type'],
                    ],
                    'assigned_at' => $this['removed_member']['assigned_at'],
                    'removed_at' => $this['timestamp']
                ],
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'key' => $project->key,
                    'links' => [
                        'self' => route('projects.show', ['project' => $project->id])
                    ]
                ],
                'stats' => [
                    'remaining_members' => $remainingMembers,
                    'roles_breakdown' => $this->getRolesBreakdown($project)
                ]
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
        })->toArray();
    }
}