<?php

namespace App\Actions\App\Project;

use App\Models\ProjectUser;

class AssignUserToRoleAction
{
    public function execute(int $projectRoleId, int $userId): ProjectUser
    {
        try {
            // Verificar si ya tiene el rol
            $exists = ProjectUser::where('project_role_id', $projectRoleId)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                throw new \Exception('El usuario ya tiene este rol');
            }

            $assignment = ProjectUser::create([
                'project_role_id' => $projectRoleId,
                'user_id' => $userId
            ]);

            if (!$assignment) {
                throw new \Exception('No se pudo asignar el usuario al rol');
            }

            return $assignment;

        } catch (\Exception $e) {
            throw new \Exception('Error al asignar usuario: ' . $e->getMessage());
        }
    }
}