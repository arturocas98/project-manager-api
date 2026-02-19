<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCreatedResource extends JsonResource
{
    public function toArray($request)
    {
        $project = $this->resource['project'] ?? null;
        $role = $this->resource['role'] ?? null;
        $assignment = $this->resource['assignment'] ?? null;

        $availableRoles = [
            ['type' => 'developers', 'description' => 'Pueden crear y editar issues, gestionar versiones'],
            ['type' => 'users', 'description' => 'Pueden crear issues y comentar'],
            ['type' => 'bug', 'description' => 'Pueden reportar bugs'],
            ['type' => 'subtask', 'description' => 'Pueden crear y gestionar subtareas']
        ];

        return [
            'data' => [
                'message' => 'Proyecto creado exitosamente',
                'project' => [
                    'id' => $project?->id,
                    'name' => $project?->name,
                    'key' => $project?->key,
                    'description' => $project?->description,
                    'created_at' => optional($project?->created_at)
                        ?->format('Y-m-d H:i:s'),

                    'user_role' => $role ? [
                        'id' => $role->id,
                        'type' => $role->type,
                        'user_id' => $assignment?->user_id,
                        'assigned_at' => optional($assignment?->created_at)
                            ?->format('Y-m-d H:i:s'),
                    ] : null,
                ],

                'available_roles' => collect($availableRoles)->map(function ($role) {
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

            'links' => $project ? [
                'self' => route('projects.show', ['project' => $project->id]),
                'parent' => route('projects.index'),
                'project' => route('projects.show', ['project' => $project->id]),
                'incidences' => route('projects.incidences.index', ['project' => $project->id]),
                'members' => route('projects.members.index', ['project' => $project->id]),
            ] : [],
        ];
    }
}
