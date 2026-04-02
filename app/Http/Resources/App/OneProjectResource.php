<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class OneProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $userRole = $this->roles->first();
        $isDetailed = $request->route()->named('projects.show');

        $data = [
            'id' => $this->id,
            'ContractNo' => $this->ContractNo,
            'client' => new ClientResource($this->whenLoaded('client')),
            'project_type' => $this->project_type,
            'objectContract' => $this->objectContract,

            'start_date' => optional($this->start_date)?->format('Y-m-d'),
            'end_date' => optional($this->end_date)?->format('Y-m-d'),
            'duration_days' => $this->duration_days,

            'administrator_email' => $this->administrator_email,
            'contracted_company' => $this->contracted_company,
            'last_phase' => $this->last_phase,

            'administrator' => $this->whenLoaded('admin', function () {
                if (! $this->admin) return null;
                return [
                    'id' => $this->admin->id,
                    'name' => $this->admin->name,
                    'email' => $this->admin->email
                ];
            }),

            // Estado del proyecto
            'state' => $this->whenLoaded('projectState', function () {
                if (! $this->projectState) return null;
                return [
                    'id' => $this->projectState->id,
                    'name' => $this->projectState->name
                ];
            }),

            // Rol del usuario autenticado
            'user_role' => $userRole ? [
                'id' => $userRole->id,
                'type' => $userRole->type,
                'permissions' => $this->getPermissions($userRole)
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

        // Si es SHOW, añadir más detalles
        if ($isDetailed) {
            $data['details'] = [
                // Todos los miembros con sus roles
                'members' => $this->getAllMembers(),

                // Estadísticas detalladas
                'stats' => [
                    'total_members' => $this->getTotalMembersCount(),
                    'total_roles' => $this->roles->count(),
                    'roles_breakdown' => $this->getRolesBreakdown(),
                ],

                // Configuración del proyecto (si aplica)
                'settings' => $this->settings ?? [
                    'is_private' => false,
                    'default_assignee' => 'unassigned',
                ],
            ];
        }

        return $data;
    }

    /**
     * Obtener permisos del rol
     */
    private function getPermissions($role): array
    {
        if (! $role->relationLoaded('permissionScheme')) {
            return [];
        }

        $scheme = $role->permissionScheme?->scheme;

        if (! $scheme || ! $scheme->relationLoaded('permissions')) {
            return [];
        }

        return $scheme->permissions->pluck('key')->toArray();
    }

    /**
     * Obtener todos los miembros con sus roles
     */
    private function getAllMembers(): array
    {
        if (! $this->relationLoaded('roles')) {
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
                            'type' => $role->type
                        ],
                        'assigned_at' => $user->pivot?->created_at?->format('Y-m-d H:i:s')
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
        if (! $this->relationLoaded('roles')) {
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
        if (! $this->relationLoaded('roles')) {
            return [];
        }

        $breakdown = [];

        foreach ($this->roles as $role) {
            $breakdown[] = [
                'role_id' => $role->id,
                'role_type' => $role->type,
                'members_count' => $role->relationLoaded('users') ? $role->users->count() : 0
            ];
        }

        return $breakdown;
    }
}
