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
                'children' => $this->when(isset($this->children), function () {
                    return collect($this->children)->map(function ($child) {
                        return [
                            'id' => $child['id'],
                            'title' => $child['title'],
                            'description' => $child['description'],
                            'priority' => $child['incidence_priority']['priority'] ?? null,
                            'created_at' => $child['created_at'],
                            'start_date' => $child['start_date'],
                            'due_date' => $child['due_date'],

                            'type' => isset($child['incidence_type']) ? [
                                'id' => $child['incidence_type']['id'],
                                'type' => $child['incidence_type']['type'],
                            ] : null,

                            'state' => isset($child['incidence_state']) ? [
                                'id' => $child['incidence_state']['id'],
                                'state' => $child['incidence_state']['state'],
                            ] : null,

                            'created_by' => isset($child['created_by']) ? [
                                'id' => $child['created_by']['id'],
                                'name' => $child['created_by']['name'],
                                'email' => $child['created_by']['email'],
                            ] : null,

                            'assigned_to' => isset($child['assigned_user']) ? [
                                'id' => $child['assigned_user']['id'],
                                'name' => $child['assigned_user']['name'],
                                'email' => $child['assigned_user']['email'],
                            ] : null,

                            // Recursivamente, los hijos de los hijos
                            'children' => !empty($child['children']) ?
                                $this->nestedChildren($child['children']) : [],
                        ];
                    });
                }),
            ],
        ];
    }

    private function nestedChildren(array $children): array
    {
        return collect($children)->map(function ($child) {
            return [
                'id' => $child['id'],
                'title' => $child['title'],
                'description' => $child['description'],
                'priority' => $child['incidence_priority']['priority'] ?? null,
                'created_at' => $child['created_at'],
                'start_date' => $child['start_date'],
                'due_date' => $child['due_date'],

                'type' => isset($child['incidence_type']) ? [
                    'id' => $child['incidence_type']['id'],
                    'type' => $child['incidence_type']['type'],
                ] : null,

                'state' => isset($child['incidence_state']) ? [
                    'id' => $child['incidence_state']['id'],
                    'state' => $child['incidence_state']['state'],
                ] : null,

                'children' => !empty($child['children']) ?
                    $this->nestedChildren($child['children']) : [],
            ];
        })->toArray();
    }
}