<?php

namespace App\Services\Project;

use App\Actions\App\Project\AssignPermissionSchemeAction;
use App\Actions\App\Project\AssignUserToRoleAction;
use App\Actions\App\Project\CreateProjectAction;
use App\Actions\App\Project\CreateProjectRoleAction;
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
                $project = $this->createProject->execute($data);

                $adminRole = $this->createRole->execute($project->id, 'administrator', 'ADM');

                $this->assignPermissions->execute($adminRole->id, 'ADM');

                $assignment = $this->assignUser->execute($adminRole->id, auth()->id());

                return [
                    'project' => $project,
                    'role' => $adminRole,
                    'assignment' => $assignment,
                ];
            });
        } catch (\Exception $e) {
            throw new ProjectException('Error en la creación del proyecto: '.$e->getMessage());
        }
    }
}
