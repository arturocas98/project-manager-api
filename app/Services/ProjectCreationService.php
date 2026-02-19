<?php

namespace App\Services;

use App\Actions\App\Project\CreateProjectAction;
use App\Actions\App\Project\CreateProjectRoleAction;
use App\Actions\App\Project\AssignPermissionSchemeAction;
use App\Actions\App\Project\AssignUserToRoleAction;
use App\Exceptions\ProjectException;
use Illuminate\Support\Facades\DB;

class ProjectCreationService
{
    public function __construct(
        private CreateProjectAction $createProject,
        private CreateProjectRoleAction $createRole,
        private AssignPermissionSchemeAction $assignPermissions,
        private AssignUserToRoleAction $assignUser
    ) {}

    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Crear proyecto
                $project = $this->createProject->execute($data);

                // 2. Crear rol de Administrador
                $adminRole = $this->createRole->execute($project->id, 'Administrators');

                // 3. Asignar esquema de permisos admin
                $this->assignPermissions->execute($adminRole->id, 'Administrators');

                // 4. Asignar usuario creador al rol admin
                $assignment = $this->assignUser->execute($adminRole->id, auth()->id());

                return [
                    'project' => $project,
                    'role' => $adminRole,
                    'assignment' => $assignment
                ];
            });
        } catch (\Exception $e) {
            throw new ProjectException('Error en la creación del proyecto: ' . $e->getMessage());
        }
    }
}
