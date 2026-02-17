<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMembersResource extends JsonResource
{
    public function toArray($request)
    {
        // Si es una colección paginada
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'data' => $this->mapMembers($this->resource->items()),
                'meta' => [
                    'current_page' => $this->resource->currentPage(),
                    'from' => $this->resource->firstItem(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'to' => $this->resource->lastItem(),
                    'total' => $this->resource->total()
                ],
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl()
                ]
            ];
        }

        // Si es un solo miembro
        return $this->mapMember($this->resource);
    }

    /**
     * Mapear colección de miembros
     */
    private function mapMembers(array $members): array
    {
        return array_map(fn($member) => $this->mapMember($member), $members);
    }

    /**
     * Mapear un miembro individual
     */
    private function mapMember($member): array
    {
        return [
            'assignment' => [
                'id' => $member->assignment_id,
                'assigned_at' => $member->assigned_at,
                'updated_at' => $member->updated_at
            ],
            'user' => [
                'id' => $member->user_id,
                'name' => $member->user_name,
                'email' => $member->user_email,
                'joined_at' => $member->user_joined_at
            ],
            'role' => [
                'id' => $member->role_id,
                'type' => $member->role_type,
                'permissions' => $this->getRolePermissions($member->role_id)
            ],
        ];
    }

    /**
     * Obtener permisos del rol (opcional, requiere consulta adicional)
     */
    private function getRolePermissions(int $roleId): array
    {
        // Implementar si quieres mostrar permisos
        return [];
    }
}