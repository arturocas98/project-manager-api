<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'assignment' => $this->whenLoaded('assignment', function () {
                return [
                    'id' => $this->assignment->id,
                    'assigned_at' => $this->assignment->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $this->assignment->updated_at?->format('Y-m-d H:i:s'), // Añadir
                ];
            }),

            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'key' => $this->project->key,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'role' => $this->whenLoaded('role', function () {
                return [
                    'id' => $this->role->id,
                    'type' => $this->role->type,
                    'permissions' => $this->getRolePermissions(),
                ];
            }),

            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member',
            ],
            'links' => $this->when(
                $this->project && $this->id,
                function () {
                    return [
                        'self' => route('projects.members.show', [
                            'project' => $this->project->id,
                            'member' => $this->id,
                        ]),
                        'parent' => route('projects.members.index', [
                            'project' => $this->project->id,
                        ]),
                    ];
                }
            ),

        ];
    }

    private function getRolePermissions(): array
    {
        if (!$this->relationLoaded('role')) {
            return [];
        }

        $role = $this->role;

        if (!$role->relationLoaded('permissionScheme')) {
            return [];
        }

        $scheme = $role->permissionScheme?->scheme;

        if (! $scheme || ! $scheme->relationLoaded('permissions')) {
            return [];
        }

        return $scheme->permissions->pluck('key')->values()->toArray();
    }
}
