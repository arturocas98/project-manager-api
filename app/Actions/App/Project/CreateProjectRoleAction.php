<?php

namespace App\Actions\App\Project;

use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
class CreateProjectRoleAction
{
    public function execute(int $projectId, string $roleCode): ProjectRole
    {
        try {

            $exists = ProjectRole::where('project_id', $projectId)
                ->where('code', $roleCode)
                ->exists();

            if ($exists) {
                throw new \Exception("El rol con código {$roleCode} ya existe en este proyecto");
            }


            $scheme = ProjectPermissionScheme::where('code', $roleCode)->first();
            $roleName = $scheme?->name ?? $this->getRoleNameFromCode($roleCode);

            $role = ProjectRole::create([
                'project_id' => $projectId,
                'type' => $roleName,
                'code' => $roleCode,
            ]);

            if (! $role) {
                throw new \Exception("No se pudo crear el rol con código {$roleCode}");
            }

            return $role;

        } catch (\Exception $e) {
            throw new \Exception('Error al crear rol: '.$e->getMessage());
        }
    }

    /**
     * Obtener nombre legible del rol por su código
     */
    private function getRoleNameFromCode(string $code): string
    {
        return match($code) {
            'ADM' => 'administrator',
            'LDR' => 'leader',
            'DEV' => 'developer',
            'TST' => 'tester',
            'DOC' => 'documenter',
            default => strtolower($code)
        };
    }
}