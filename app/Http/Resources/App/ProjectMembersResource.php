<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMembersResource extends JsonResource
{
    public function toArray($request)
    {
        $projectId = $request->route('project');

        // Si es una colección paginada
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'data' => $this->mapMembers($this->resource->items()),
                'meta' => [
                    'api_version' => '1.0.0',
                    'timestamp' => now()->toIso8601String(),
                    'resource_type' => 'project_members',
                    'pagination' => [
                        'current_page' => $this->resource->currentPage(),
                        'from' => $this->resource->firstItem(),
                        'last_page' => $this->resource->lastPage(),
                        'per_page' => $this->resource->perPage(),
                        'to' => $this->resource->lastItem(),
                        'total' => $this->resource->total(),
                    ],
                    'project' => [
                        'id' => $projectId,
                    ],
                ],
                'links' => [
                    'self' => $this->resource->url($this->resource->currentPage()),
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                    'parent' => route('projects.show', ['project' => $projectId]),
                    'create' => route('projects.members.store', ['project' => $projectId]),
                ],
            ];
        }

        // Si es un solo miembro
        return [
            'data' => $this->mapMember($this->resource),
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member',
                'project_id' => $projectId,
            ],
            'links' => [
                'self' => route('projects.members.show', [
                    'project' => $projectId,
                    'member' => $this->resource->assignment_id ?? $this->resource['assignment']['id'] ?? null,
                ]),
                'parent' => route('projects.members.index', ['project' => $projectId]),
                'project' => route('projects.show', ['project' => $projectId]),
            ],
        ];
    }

    /**
     * Mapear colección de miembros
     */
    private function mapMembers(array $members): array
    {
        return array_map(fn ($member) => $this->mapMember($member), $members);
    }

    /**
     * Mapear un miembro individual
     */
    private function mapMember($member): array
    {
        $projectId = request()->route('project');
        $assignmentId = $member->assignment_id ?? $member['assignment']['id'] ?? null;

        return [
            'assignment' => [
                'id' => $member->assignment_id ?? $member['assignment']['id'] ?? null,
                'assigned_at' => $member->assigned_at ?? $member['assignment']['assigned_at'] ?? null,
                'updated_at' => $member->updated_at ?? $member['assignment']['updated_at'] ?? null,
                'links' => $assignmentId ? [
                    'self' => route('projects.members.show', [
                        'project' => $projectId,
                        'member' => $assignmentId,
                    ]),
                    'delete' => route('projects.members.destroy', [
                        'project' => $projectId,
                        'member' => $assignmentId,
                    ]),
                ] : [],
            ],
            'user' => [
                'id' => $member->user_id ?? $member['user']['id'] ?? null,
                'name' => $member->user_name ?? $member['user']['name'] ?? null,
                'email' => $member->user_email ?? $member['user']['email'] ?? null,
                'joined_at' => $member->user_joined_at ?? $member['user']['joined_at'] ?? null,
            ],
            'role' => [
                'id' => $member->role_id ?? $member['role']['id'] ?? null,
                'type' => $member->role_type ?? $member['role']['type'] ?? null,
                'permissions' => $this->getRolePermissions($member->role_id ?? $member['role']['id'] ?? null),
            ],
        ];
    }

    /**
     * Obtener permisos del rol
     */
    private function getRolePermissions($roleId): array
    {
        if (! $roleId) {
            return [];
        }

        // Aquí mantén tu lógica existente para obtener permisos
        return [];
    }
}
