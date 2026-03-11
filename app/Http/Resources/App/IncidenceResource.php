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
                'priority' => $this->incidencePriority?->priority,
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

                'category' => $this->category ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'description' => $this->category->description,
                    'code' => $this->category->code,
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
                        ->where('project_id', $this->project_id)
                        ->first()
                        ?->type
                ] : null,

                'parent' => $this->parentIncidence ? [
                    'id' => $this->parentIncidence->id,
                    'title' => $this->parentIncidence->title,
                ] : null,

                // Aquí van los hijos ordenados cronológicamente
                'children' => $this->childIncidences->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'title' => $child->title,
                        'description' => $child->description,
                        'priority' => $child->incidencePriority?->priority,
                        'created_at' => $child->created_at,
                        'start_date' => $child->start_date,
                        'due_date' => $child->due_date,

                        'type' => $child->incidenceType ? [
                            'id' => $child->incidenceType->id,
                            'type' => $child->incidenceType->type,
                        ] : null,

                        'state' => $child->incidenceState ? [
                            'id' => $child->incidenceState->id,
                            'state' => $child->incidenceState->state,
                        ] : null,

                        'children' => $this->nestedChildren($child->childIncidences),
                    ];
                })->toArray(),
            ],
        ];
    }

    private function nestedChildren($children): array
    {
        return $children->map(function ($child) {

            return [
                'id' => $child->id,
                'title' => $child->title,
                'description' => $child->description,
                'priority' => $child->incidencePriority?->priority,
                'created_at' => $child->created_at,
                'start_date' => $child->start_date,
                'due_date' => $child->due_date,

                'type' => $child->incidenceType ? [
                    'id' => $child->incidenceType->id,
                    'type' => $child->incidenceType->type,
                ] : null,

                'state' => $child->incidenceState ? [
                    'id' => $child->incidenceState->id,
                    'state' => $child->incidenceState->state,
                ] : null,

                // aquí está la recursividad
                'children' => $this->nestedChildren($child->childIncidences)
            ];

        })->values()->toArray();
    }
}