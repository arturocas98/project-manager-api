<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberRoleUpdatedResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'data' => [
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'assignment' => [
                    'id' => $this['assignment']->id,
                    'updated_at' => $this['assignment']->updated_at->format('Y-m-d H:i:s')
                ],
                'user' => [
                    'id' => $this['user']->id,
                    'name' => $this['user']->name,
                    'email' => $this['user']->email
                ],
                'changes' => [
                    'from' => [
                        'id' => $this['changes']['from']['id'],
                        'type' => $this['changes']['from']['type']
                    ],
                    'to' => [
                        'id' => $this['changes']['to']['id'],
                        'type' => $this['changes']['to']['type']
                    ]
                ],
                'project' => [
                    'id' => $this['project']->id,
                    'name' => $this['project']->name,
                    'key' => $this['project']->key
                ],
                'updated_at' => $this['timestamp']
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_member_role_update',
                'action' => 'update_role',
                'status' => 'success'
            ],
            'links' => [
                'self' => route('projects.members.show', [
                    'project' => $this['project']->id,
                    'member' => $this['assignment']->id
                ]),
                'parent' => route('projects.members.index', ['project' => $this['project']->id]),
                'project' => route('projects.show', ['project' => $this['project']->id]),
                'member' => route('projects.members.show', [
                    'project' => $this['project']->id,
                    'member' => $this['assignment']->id
                ]),
                'members' => route('projects.members.index', ['project' => $this['project']->id])
            ]
        ];
    }
}