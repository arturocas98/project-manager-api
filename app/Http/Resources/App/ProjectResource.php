<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{

    public function toArray($request)
    {
        $userRole = $this->roles->first();

        return [
            'data' => [
                'id' => $this->id,
                'ContractNo' => $this->ContractNo,
                'client' => $this->client,
                'project_type' => $this->project_type,
                'objectContract' => $this->objectContract,

                'start_date' => optional($this->start_date)?->format('Y-m-d'),
                'end_date' => optional($this->end_date)?->format('Y-m-d'),
                'duration_days' => $this->duration_days,

                'administrator_email' => $this->administrator_email,
                'contracted_company' => $this->contracted_company,
                'last_phase' => $this->last_phase,

                'administrator' => $this->whenLoaded('admin', function () {
                    return [
                        'id' => $this->admin->id,
                        'name' => $this->admin->name,
                        'email' => $this->admin->email,
                    ];
                }),

                'state' => $this->whenLoaded('projectState', function () {
                    return [
                        'id' => $this->projectState->id,
                        'name' => $this->projectState->name,
                    ];
                }),

                'user_role' => $userRole ? [
                    'id' => $userRole->id,
                    'type' => $userRole->type,
                ] : null,

                'stats' => [
                    'members_count' => $this->whenLoaded('roles', function () {
                        return $this->roles->sum(function ($role) {
                            return $role->users->count();
                        });
                    }, 0),
                ],
            ],
            'meta' => [
                'timestamps' => [
                    'created' => $this->created_at?->toIso8601String(),
                    'updated' => $this->updated_at?->toIso8601String(),
                ],
                'type' => 'project',
            ],
            'links' => [
                'self' => route('projects.show', $this->id),
                'update' => route('projects.update', $this->id),
                'delete' => route('projects.destroy', $this->id),
                'members' => route('projects.members.index', $this->id),
            ],
        ];
    }

    /**
     * Método adicional para cuando se necesita metadata personalizada
     */
    public function with($request)
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
            'links' => [
                'collection' => route('projects.index'),
                'create' => route('projects.store'),
            ],
        ];
    }
}
