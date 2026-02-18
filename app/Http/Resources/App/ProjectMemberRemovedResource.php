<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberRemovedResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => true,
            'message' => 'Miembro eliminado exitosamente del proyecto',
            'data' => [
                'removed_member' => [
                    'assignment_id' => $this['removed_member']['assignment_id'],
                    'user' => [
                        'id' => $this['removed_member']['user_id'],
                        'name' => $this['removed_member']['user_name'],
                        'email' => $this['removed_member']['user_email']
                    ],
                    'role' => [
                        'id' => $this['removed_member']['role_id'],
                        'type' => $this['removed_member']['role_type']
                    ],
                    'assigned_at' => $this['removed_member']['assigned_at'],
                    'removed_at' => $this['timestamp']
                ],
                'project' => [
                    'id' => $this['project']->id,
                    'name' => $this['project']->name,
                    'key' => $this['project']->key
                ]
            ],
            'stats' => [
                'remaining_members' => $this['project']->roles()
                    ->withCount('users')
                    ->get()
                    ->sum('users_count')
            ],
            'links' => [
                'project' => route('projects.show', $this['project']->id),
            ]
        ];
    }
}