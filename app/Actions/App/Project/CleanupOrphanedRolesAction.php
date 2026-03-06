<?php

namespace App\Actions\App\Project;

use App\Models\ProjectRole;
use App\Models\ProjectRolePermission;
use App\Models\ProjectUser;

class CleanupOrphanedRolesAction
{
    /**
     * Eliminar roles que ya no tienen usuarios asociados
     */
    public function execute(ProjectRole $role): void
    {

        $role->refresh();


        $hasUsers = ProjectUser::where('project_role_id', $role->id)
            ->whereNull('deleted_at')
            ->exists();


        if (!$hasUsers && !$this->isDefaultRole($role)) {
            \DB::transaction(function () use ($role) {

                ProjectRolePermission::where('project_role_id', $role->id)->delete();


                $role->delete();
            });
        }
    }

    /**
     * Verificar si es un rol que no debe eliminarse automáticamente
     */
    private function isDefaultRole(ProjectRole $role): bool
    {

        $defaultRoles = ['administrators', 'members', 'viewers'];

        return in_array($role->type, $defaultRoles);
    }
}