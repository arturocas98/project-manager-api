<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCreatedResource extends JsonResource
{
    public function toArray($request)
    {
        $availableRoles = [
            ['type' => 'Developers', 'description' => 'Pueden crear y editar issues, gestionar versiones'],
            ['type' => 'Users', 'description' => 'Pueden crear issues y comentar'],
            ['type' => 'bug', 'description' => 'Pueden reportar bugs'],
            ['type' => 'subtask', 'description' => 'Pueden crear y gestionar subtareas']
        ];

        return [
            'data' => [
                'message' => 'Proyecto creado exitosamente',
                'project' => [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'key' => $this->project->key,
                    'description' => $this->project->description,
                    'created_at' => $this->project->created_at->format('Y-m-d H:i:s'),
                    'user_role' => [
                        'id' => $this->role->id,
                        'type' => $this->role->type,
                        'user_id' => $this->assignment->user_id,
                        'assigned_at' => $this->assignment->created_at->format('Y-m-d H:i:s'),
                    ],
                ],
                'available_roles' => collect($availableRoles)->map(function ($role) use ($request) {
                    return [
                        'type' => $role['type'],
                        'description' => $role['description'],
                    ];
                })->toArray(),
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_creation',
                'action' => 'create',
                'status' => 'success',
            ],
            'links' => [
                'self' => route('projects.show', ['project' => $this->project->id]),
                'parent' => route('projects.index'),
                'project' => route('projects.show', ['project' => $this->project->id]),
                'incidences' => route('projects.incidences.index', ['project' => $this->project->id]),
                'members' => route('projects.members.index', ['project' => $this->project->id]),
            ],
        ];
    }
}