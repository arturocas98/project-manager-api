<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectMemberCollection extends ResourceCollection
{
    public function toArray($request)
    {
        // Obtener todos los role_ids de la colección
        $roleIds = $this->collection->pluck('role_id')->unique()->toArray();

        // Cargar permisos de todos los roles de una sola vez (evita N+1)
        $permissionsByRole = $this->getPermissionsForRoles($roleIds);

        return [
            'data' => $this->collection->map(function ($member) use ($permissionsByRole) {
                return [
                    'assignment' => [
                        'id' => $member->assignment_id,
                        'assigned_at' => date('Y-m-d H:i:s', strtotime($member->assigned_at)),
                    ],
                    'user' => [
                        'id' => $member->user_id,
                        'name' => $member->user_name,
                        'email' => $member->user_email,
                    ],
                    'role' => [
                        'id' => $member->role_id,
                        'type' => $member->role_type,
                        'permissions' => $permissionsByRole[$member->role_id] ?? [],
                    ],
                    'links' => [
                        'self' => route('projects.members.show', [
                            'project' => request()->route('project'),
                            'member' => $member->assignment_id,
                        ]),
                        'remove' => route('projects.members.destroy', [
                            'project' => request()->route('project'),
                            'member' => $member->assignment_id,
                        ]),
                    ],
                ];
            }),
            'meta' => [
                'current_page' => $this->resource->currentPage(),
                'from' => $this->resource->firstItem(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'to' => $this->resource->lastItem(),
                'total' => $this->resource->total(),
            ],
            'links' => [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ],
        ];
    }

    /**
     * Obtener permisos para múltiples roles de una sola vez
     */
    private function getPermissionsForRoles(array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        // Obtener todos los esquemas de permisos asociados a estos roles
        $rolePermissions = \DB::table('project_role_permissions')
            ->whereIn('project_role_id', $roleIds)
            ->get()
            ->keyBy('project_role_id');

        if (empty($rolePermissions)) {
            return [];
        }

        $schemeIds = $rolePermissions->pluck('permission_scheme_id')->unique()->toArray();

        // Obtener todos los permisos de esos esquemas
        $permissions = \DB::table('scheme_permissions')
            ->join('project_permissions', 'project_permissions.id', '=', 'scheme_permissions.project_permission_id')
            ->whereIn('scheme_permissions.permission_scheme_id', $schemeIds)
            ->select([
                'scheme_permissions.permission_scheme_id',
                'project_permissions.key',
            ])
            ->get()
            ->groupBy('permission_scheme_id')
            ->map(function ($items) {
                return $items->pluck('key')->toArray();
            })
            ->toArray();

        // Mapear role_id -> lista de permisos
        $result = [];
        foreach ($rolePermissions as $roleId => $rp) {
            $result[$roleId] = $permissions[$rp->permission_scheme_id] ?? [];
        }

        return $result;
    }
}
