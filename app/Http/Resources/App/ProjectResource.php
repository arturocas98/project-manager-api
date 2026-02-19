<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $userRole = null;

        if ($request->user()) {
            $userRole = $this->getUserRole($request->user()->id);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'description' => $this->description,

            'created_at' => optional($this->created_at)
                ?->format('Y-m-d H:i:s'),

            // Usuario creador
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
        ];
    }
}
