<?php

namespace App\Actions\App\Project;

use App\Models\Project;
class UpdateProjectAction
{
    /**
     * Actualizar un proyecto
     *
     * @throws \Exception
     */
    public function execute(Project $project, array $data): Project
    {
        try {

            $fillable = array_intersect_key($data, array_flip([
                'ContractNo',
                'client',
                'project_type',
                'start_date',
                'duration_days',
                'end_date',
                'administrator_email',
                'contracted_company',
                'last_phase',
                'project_state_id',
                'objectContract',
            ]));

            if (empty($fillable)) {
                throw new \Exception('No hay datos para actualizar');
            }

            $project->update($fillable);

            $project->refresh();

            return $project;

        } catch (\Throwable $e) {
            throw new \Exception('Error al actualizar el proyecto: '.$e->getMessage());
        }
    }
}