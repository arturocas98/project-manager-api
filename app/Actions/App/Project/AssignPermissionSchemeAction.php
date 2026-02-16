<?php

namespace App\Actions\App\Project;

use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRolePermission;

class AssignPermissionSchemeAction
{
    public function execute(int $projectRoleId, string $schemeName): ProjectRolePermission
    {
        try {
            $scheme = ProjectPermissionScheme::where('name', $schemeName)->first();

            if (!$scheme) {
                throw new \Exception("Esquema de permisos '{$schemeName}' no encontrado");
            }

            $permission = ProjectRolePermission::create([
                'project_role_id' => $projectRoleId,
                'permission_scheme_id' => $scheme->id
            ]);

            if (!$permission) {
                throw new \Exception('No se pudo asignar el esquema de permisos');
            }

            return $permission;

        } catch (\Exception $e) {
            throw new \Exception('Error al asignar permisos: ' . $e->getMessage());
        }
    }
}
