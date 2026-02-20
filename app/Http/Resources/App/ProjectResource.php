<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
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

                // Rol del usuario autenticado
                'user_role' => $userRole ? [
                    'id' => $userRole->id,
                    'type' => $userRole->type,
                ] : null,

                // Estadísticas
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
                // Relaciones
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