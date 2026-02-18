<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        // El rol del usuario autenticado en este proyecto
        $userRole = $this->roles->first();

        return [
            'data' => [
                'id' => $this->id,
                'name' => $this->name,
                'key' => $this->key,
                'description' => $this->description,
                'created_at' => $this->created_at->format('Y-m-d H:i:s'),

                // Usuario que creó el proyecto
                'created_by' => $this->whenLoaded('createdBy', function() {
                    return [
                        'id' => $this->createdBy->id,
                        'name' => $this->createdBy->name,
                        'email' => $this->createdBy->email,
                    ];
                }),

                // ROL DEL USUARIO AUTENTICADO EN ESTE PROYECTO
                'user_role' => $userRole ? [
                    'id' => $userRole->id,
                    'type' => $userRole->type,
                ] : null,

                // Estadísticas rápidas (opcional)
                'stats' => [
                    'members_count' => $this->whenLoaded('roles', function() {
                        return $this->roles->sum(function($role) {
                            return $role->users->count();
                        });
                    }, 0)
                ],
            ],
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project',
            ],
            'links' => [
                'self' => route('projects.show', ['project' => $this->id]),
                'parent' => route('projects.index'),
                'incidences' => route('projects.incidences.index', ['project' => $this->id]),
                'members' => route('projects.members.index', ['project' => $this->id]),
            ],
        ];
    }

    /**
     * Obtener los permisos del rol
     */
    private function getPermissions($role): array
    {
        if (!$role->relationLoaded('permissionScheme')) {
            return [];
        }

        $scheme = $role->permissionScheme?->scheme;

        if (!$scheme || !$scheme->relationLoaded('permissions')) {
            return [];
        }

        return $scheme->permissions->pluck('key')->toArray();
    }
}