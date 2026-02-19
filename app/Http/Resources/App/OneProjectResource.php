<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;
class OneProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $userRole = $this->roles->first();
        $isDetailed = $request->route()->named('projects.show');

        return [
            'data' => $this->getData($userRole, $isDetailed),
            'meta' => [
                'api_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'resource_type' => 'project',
                'response_type' => $isDetailed ? 'detailed' : 'summary',
                'available_relations' => $this->getAvailableRelations(),
            ],
            'links' => [
                'self' => route('projects.show', ['project' => $this->id]),
                'parent' => route('projects.index'),
                'incidences' => route('projects.incidences.index', ['project' => $this->id]),
                'members' => route('projects.members.index', ['project' => $this->id]),
                ...($isDetailed ? [
                    'update' => route('projects.update', ['project' => $this->id]),
                    'delete' => route('projects.destroy', ['project' => $this->id]),
                ] : []),
            ],
        ];
    }

    /**
     * Obtener los datos del proyecto
     */
    private function getData($userRole, $isDetailed): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Usuario que creó el proyecto
            'created_by' => $this->whenLoaded('createdBy', function() {
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
                'permissions' => $this->getPermissions($userRole),
            ] : null,
        ];

        // Si es SHOW, añadir más detalles
        if ($isDetailed) {
            $data['details'] = [
                // Todos los miembros con sus roles
                'members' => $this->getAllMembers(),

                // Estadísticas detalladas
                'stats' => [
                    'total_members' => $this->getTotalMembersCount(),
                    'total_roles' => $this->roles->count(),
                    'roles_breakdown' => $this->getRolesBreakdown()
                ],

                // Configuración del proyecto (si aplica)
                'settings' => $this->settings ?? [
                        'is_private' => false,
                        'default_assignee' => 'unassigned'
                    ]
            ];
        }

        return $data;
    }

    /**
     * Obtener permisos del rol
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

    /**
     * Obtener todos los miembros con sus roles
     */
    private function getAllMembers(): array
    {
        if (!$this->relationLoaded('roles')) {
            return [];
        }

        $members = [];

        foreach ($this->roles as $role) {
            if ($role->relationLoaded('users')) {
                foreach ($role->users as $user) {
                    $members[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => [
                            'id' => $role->id,
                            'type' => $role->type,
                        ],
                        'assigned_at' => $user->pivot?->created_at?->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        // Ordenar por fecha de asignación
        usort($members, fn($a, $b) => strtotime($b['assigned_at']) - strtotime($a['assigned_at']));

        return $members;
    }

    /**
     * Contar total de miembros únicos
     */
    private function getTotalMembersCount(): int
    {
        if (!$this->relationLoaded('roles')) {
            return 0;
        }

        $userIds = [];
        foreach ($this->roles as $role) {
            if ($role->relationLoaded('users')) {
                foreach ($role->users as $user) {
                    $userIds[$user->id] = true;
                }
            }
        }

        return count($userIds);
    }

    /**
     * Desglose de miembros por rol
     */
    private function getRolesBreakdown(): array
    {
        if (!$this->relationLoaded('roles')) {
            return [];
        }

        $breakdown = [];

        foreach ($this->roles as $role) {
            $breakdown[] = [
                'role_id' => $role->id,
                'role_type' => $role->type,
                'members_count' => $role->relationLoaded('users') ? $role->users->count() : 0,
            ];
        }

        return $breakdown;
    }

    /**
     * Obtener relaciones disponibles
     */
    private function getAvailableRelations(): array
    {
        $relations = [];

        if ($this->relationLoaded('createdBy')) {
            $relations[] = 'created_by';
        }

        if ($this->relationLoaded('roles')) {
            $relations[] = 'roles';

            if ($this->roles->isNotEmpty() && $this->roles->first()->relationLoaded('users')) {
                $relations[] = 'members';
            }
        }

        return $relations;
    }
}