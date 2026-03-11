<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCreatedResource extends JsonResource
{
    public function toArray($request)
    {
        $availableRoles = [
            ['type' => 'administrator', 'description' => 'Pueden crear, borrar, editar todo a su antojo'],
            ['type' => 'manager', 'description' => 'Pueden crear, editar, y borrar issues, gestionarlas con los developers'],
            ['type' => 'developers', 'description' => 'Pueden crear y editar issues, gestionar versiones'],
            ['type' => 'users', 'description' => 'Pueden visualizar issues y comentar'],
            ['type' => 'bug', 'description' => 'Pueden reportar bugs'],
            ['type' => 'subtask', 'description' => 'Pueden crear y gestionar subtareas'],
        ];

        return [
            'data' => [
                'message' => 'Proyecto creado exitosamente',
                'project' => [
                    'id' => $this->project->id,
                    'ContractNo' => $this->project->ContractNo,
                    'client' => $this->project->client,
                    'project_type' => $this->project->project_type,
                    'objectContract' => $this->project->objectContract,

                    'start_date' => optional($this->project->start_date)?->format('Y-m-d'),
                    'end_date' => optional($this->project->end_date)?->format('Y-m-d'),
                    'duration_days' => $this->project->duration_days,

                    'administrator_email' => $this->project->administrator_email,
                    'contracted_company' => $this->project->contracted_company,
                    'last_phase' => $this->project->last_phase,

                    'user_role' => [
                        'id' => $this->role->id,
                        'type' => $this->role->type,
                        'user_id' => $this->assignment->user_id,
                        'assigned_at' => $this->assignment->created_at->format('Y-m-d H:i:s')
                    ]
                ],

                'available_roles' => collect($availableRoles)->map(function ($role) {
                    return [
                        'type' => $role['type'],
                        'description' => $role['description'],
                    ];
                })->values()->toArray(),
            ],

            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project_creation',
                'action' => 'create',
                'status' => 'success',
            ],

            'links' => $this ? [
                'self' => route('projects.show', ['project' => $this->project->id,]),
                'parent' => route('projects.index'),
                'project' => route('projects.show', ['project' =>  $this->project->id,]),
                'incidences' => route('projects.incidences.index', ['project' =>  $this->project->id,]),
                'members' => route('projects.members.index', ['project' =>  $this->project->id,]),
            ] : [],
        ];
    }
}
