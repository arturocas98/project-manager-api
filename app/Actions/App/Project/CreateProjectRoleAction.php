<?php

namespace App\Actions\App\Project;

use App\Models\ProjectRole;

class CreateProjectRoleAction
{
    public function execute(int $projectId, string $roleType): ProjectRole
    {
        try {
            // Verificar si el rol ya existe
            $exists = ProjectRole::where('project_id', $projectId)
                ->where('type', $roleType)
                ->exists();

            if ($exists) {
                throw new \Exception("El rol {$roleType} ya existe en este proyecto");
            }

            $role = ProjectRole::create([
                'project_id' => $projectId,
                'type' => $roleType,
            ]);

            if (! $role) {
                throw new \Exception("No se pudo crear el rol {$roleType}");
            }

            return $role;

        } catch (\Exception $e) {
            throw new \Exception('Error al crear rol: '.$e->getMessage());
        }
    }
}
