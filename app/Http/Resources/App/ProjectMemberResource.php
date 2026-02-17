<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'assignment' => [
                'id' => $this->assignment->id,
                'assigned_at' => $this->assignment->created_at->format('Y-m-d H:i:s')
            ],
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'key' => $this->project->key
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email
            ],
            'role' => [
                'id' => $this->role->id,
                'type' => $this->role->type,
                'permissions' => $this->getRolePermissions($this->role)
            ],
            'links' => [
                'project' => route('projects.show', $this->project->id),
                'user' => route('users.show', $this->user->id),
                'remove' => route('projects.members.destroy', [
                    'project' => $this->project->id,
                    'member' => $this->assignment->id
                ])
            ]
        ];
    }

    private function getRolePermissions($role): array
    {
        if (!$role->relationLoaded('permissionScheme')) {
            return [];
        }

        $scheme = $role->permissionScheme?->scheme;

        if (!$scheme || !$scheme->relationLoaded('permissions')) {
            return [];
        }

        return $scheme->permissions->pluck('key')->toArray();
    }
}
