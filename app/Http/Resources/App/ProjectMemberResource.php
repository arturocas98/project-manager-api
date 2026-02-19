<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'assignment' => [
                    'id' => $this->assignment->id,
                    'assigned_at' => $this->assignment->created_at->format('Y-m-d H:i:s'),
                ],
                'project' => [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'key' => $this->project->key,
                ],
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                'role' => [
                    'id' => $this->role->id,
                    'type' => $this->role->type,
                    'permissions' => $this->getRolePermissions($this->role),
                ],
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member',
            ],
            'links' => [
                'self' => route('projects.members.show', [
                    'project' => $this->project->id,
                    'member' => $this->assignment->id,
                ]),
                'parent' => route('projects.members.index', ['project' => $this->project->id]),
            ],
        ];
    }

    private function getRolePermissions($role): array
    {
        if (! $role->relationLoaded('permissionScheme')) {
            return [];
        }

        $scheme = $role->permissionScheme?->scheme;

        if (! $scheme || ! $scheme->relationLoaded('permissions')) {
            return [];
        }

        return $scheme->permissions->pluck('key')->toArray();
    }
}
