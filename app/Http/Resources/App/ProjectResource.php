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
                'name' => $this->name,
                'key' => $this->key,
                'description' => $this->description,

                'created_at' => optional($this->created_at)
                    ?->format('Y-m-d H:i:s'),

                'created_by' => $this->whenLoaded('createdBy', function () {
                    return [
                        'id' => $this->createdBy->id,
                        'name' => $this->createdBy->name,
                        'email' => $this->createdBy->email,
                    ];
                }),


                'user_role' => $userRole ? [
                    'id' => $userRole->id,
                    'type' => $userRole->type,
                    'code' => $userRole->code,
                ] : null,


                'members' => $this->whenLoaded('projectUsers', function () {
                    return $this->projectUsers->map(function ($projectUser) {
                        return [
                            'id' => $projectUser->user->id,
                            'name' => $projectUser->user->name,
                            'email' => $projectUser->user->email,
                            'role' => [
                                'id' => $projectUser->role->id,
                                'type' => $projectUser->role->type,
                                'code' => $projectUser->role->code,
                            ],
                            'assigned_at' => $projectUser->created_at?->format('Y-m-d H:i:s'),
                        ];
                    })->values();
                }, []),


                'stats' => [
                    'members_count' => $this->whenLoaded('projectUsers', function () {
                        return $this->projectUsers->count();
                    }, 0),

                    'total_incidences' => $this->whenLoaded('incidences', function () {
                        return $this->incidences->count();
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
                'incidences' => route('projects.incidences.index', $this->id),
            ],
        ];
    }

    /**
     * Método adicional para metadata personalizada
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