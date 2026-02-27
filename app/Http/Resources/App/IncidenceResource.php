<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->incidencePriority->priority,
                'project_id' => $this->project_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'due_date' => $this->due_date,
                'start_date' => $this->start_date,

                'type' => $this->incidenceType ? [
                    'id' => $this->incidenceType->id,
                    'type' => $this->incidenceType->type,
                ] : null,

                'state' => $this->incidenceState ? [
                    'id' => $this->incidenceState->id,
                    'state' => $this->incidenceState->state,
                ] : null,

                'created_by' => $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ] : null,

                'assigned_to' => $this->assignedUser ? [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                    'role_name' => $this->assignedUser
                        ->projectRoles()
                        ->where('project_id', $this->project_id)  // Filtramos por el proyecto de la incidencia
                        ->first()
                        ?->type  // El campo 'type' en ProjectRole tiene el nombre del rol
                ] : null,

                'parent' => $this->parentIncidence ? [
                    'id' => $this->parentIncidence->id,
                    'title' => $this->parentIncidence->title,
                ] : null,
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'incidence',
            ],
            'links' => [
                'self' => route('projects.incidences.store', [
                    'project' => $this->project_id,
                    'incidence' => $this->id
                ]),
                'parent' => route('projects.incidences.index', [
                    'project' => $this->project_id
                ]),
            ],
        ];
    }
}