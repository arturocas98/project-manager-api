<?php

namespace App\Actions\App\Project;

use App\Models\Project;
use Illuminate\Support\Str;


class CreateProjectAction
{
    public function execute(array $data): Project
    {
        try {

            $project = Project::create([
                'ContractNo' => $data['ContractNo'],
                'client' => $data['client'],
                'project_type' => $data['project_type'],
                'start_date' => $data['start_date'],

                'duration_days' => $data['duration_days'] ?? null,
                'end_date' => $data['end_date'] ?? null,

                'administrator' => auth()->id(),
                'administrator_email' => $data['administrator_email'] ?? null,

                'contracted_company' => $data['contracted_company'] ?? null,
                'last_phase' => $data['last_phase'] ?? null,
                'project_state_id' => $data['project_state_id'] ?? null,

                'objectContract' => $data['objectContract'] ?? null,
            ]);

            if (!$project) {
                throw new \Exception('No se pudo crear el proyecto');
            }

            return $project;

        } catch (\Throwable $e) {
            throw new \Exception('Error al crear proyecto: ' . $e->getMessage());
        }
    }
}