<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMembersResource extends JsonResource
{
    public function toArray($request)
    {
        $projectId = $request->route('project');

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
                'links' => $this->getPaginationLinks($projectId)
            ];
        }

        return [
            'data' => $this->mapMember($this->resource),
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member',
                'project_id' => $projectId,
            ],
            'links' => $this->getMemberLinks($projectId, $this->resource)
        ];
    }

    private function mapMembers(array $members): array
    {
        return array_map(fn($member) => $this->mapMember($member), $members);
    }

    private function mapMember($member): array
    {
        $projectId = request()->route('project');

        // Si $member es un modelo ProjectUser (como en tu query original)
        if ($member instanceof \App\Models\ProjectUser) {
            return $this->mapProjectUser($member, $projectId);
        }

        // Si es un array (por si acaso)
        if (is_array($member)) {
            return $this->mapArrayMember($member, $projectId);
        }

        return [];
    }

    private function mapProjectUser($projectUser, $projectId): array
    {
        return [
            'assignment' => [
                'id' => $projectUser->id,
                'assigned_at' => $projectUser->created_at?->toIso8601String(),
                'updated_at' => $projectUser->updated_at?->toIso8601String(),
                'links' => [
                    'self' => route('projects.members.show', [
                        'project' => $projectId,
                        'member' => $projectUser->id
                    ]),
                    'delete' => route('projects.members.destroy', [
                        'project' => $projectId,
                        'member' => $projectUser->id
                    ])
                ]
            ],
            'user' => [
                'id' => $projectUser->user?->id,
                'name' => $projectUser->user?->name,
                'email' => $projectUser->user?->email,
                'joined_at' => $projectUser->user?->created_at?->toIso8601String(),
            ],
            'role' => [
                'id' => $projectUser->role?->id,
                'type' => $projectUser->role?->type,
                'permissions' => $this->getRolePermissions($projectUser->role),
            ],
        ];
    }

    private function mapArrayMember(array $member, $projectId): array
    {
        return [
            'assignment' => [
                'id' => $member['id'] ?? null,
                'assigned_at' => isset($member['created_at'])
                    ? \Carbon\Carbon::parse($member['created_at'])->toIso8601String()
                    : null,
                'updated_at' => isset($member['updated_at'])
                    ? \Carbon\Carbon::parse($member['updated_at'])->toIso8601String()
                    : null,
                'links' => [
                    'self' => route('projects.members.show', [
                        'project' => $projectId,
                        'member' => $member['id'] ?? null
                    ]),
                    'delete' => route('projects.members.destroy', [
                        'project' => $projectId,
                        'member' => $member['id'] ?? null
                    ])
                ]
            ],
            'user' => [
                'id' => $member['user_id'] ?? $member['user']['id'] ?? null,
                'name' => $member['user']['name'] ?? null,
                'email' => $member['user']['email'] ?? null,
                'joined_at' => isset($member['user']['created_at'])
                    ? \Carbon\Carbon::parse($member['user']['created_at'])->toIso8601String()
                    : null,
            ],
            'role' => [
                'id' => $member['project_role_id'] ?? $member['role']['id'] ?? null,
                'type' => $member['role']['type'] ?? null,
                'permissions' => $this->getRolePermissionsById(
                    $member['project_role_id'] ?? $member['role']['id'] ?? null
                ),
            ],
        ];
    }

    private function getRolePermissions($role): array
    {
        if (!$role) {
            return [];
        }

        return $role->permissions ?? [];
    }

    private function getRolePermissionsById($roleId): array
    {
        if (! $roleId) {
            return [];
        }

        // Implementa según necesites
        return [];
    }

    private function getPaginationLinks($projectId): array
    {
        return [
            'self' => $this->resource->url($this->resource->currentPage()),
            'first' => $this->resource->url(1),
            'last' => $this->resource->url($this->resource->lastPage()),
            'prev' => $this->resource->previousPageUrl(),
            'next' => $this->resource->nextPageUrl(),
            'parent' => route('projects.show', ['project' => $projectId]),
            'create' => route('projects.members.store', ['project' => $projectId])
        ];
    }

    private function getMemberLinks($projectId, $member): array
    {
        $assignmentId = $member instanceof \App\Models\ProjectUser
            ? $member->id
            : ($member['id'] ?? null);

        return [
            'self' => route('projects.members.show', [
                'project' => $projectId,
                'member' => $assignmentId
            ]),
            'parent' => route('projects.members.index', ['project' => $projectId]),
            'project' => route('projects.show', ['project' => $projectId])
        ];
    }
}
