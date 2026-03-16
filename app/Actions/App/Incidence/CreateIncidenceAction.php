<?php

namespace App\Actions\App\Incidence;

use App\Exceptions\IncidenceException;
use App\Models\Incidence;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateIncidenceAction
{
    public function execute(int $projectId, array $data, int $createdById): Incidence
    {
        try {
            DB::beginTransaction();

            $incidenceData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'incidence_priority_id' => $data['incidence_priority_id'],
                'project_id' => $projectId,
                'incidence_type_id' => $data['incidence_type_id'],
                'incidence_category_id' => $data['incidence_category_id'],
                'incidence_state_id' => $data['incidence_state_id']??1,
                'created_by_id' => $createdById,
                'start_date' => $data['start_date'],
                'due_date' => $data['due_date'],
                'assigned_user_id' => $data['assigned_user_id']??null,
                'parent_incidence_id' => $data['parent_incidence_id'] ?? null,
            ];

            $this->validateBusinessRules($incidenceData);

            $incidence = Incidence::create($incidenceData);

            if ($incidence->incidence_type_id == 3 && $incidence->assigned_user_id) {

                $incidence->load('project');

                Notification::create([
                    'user_id' => $incidence->assigned_user_id,
                    'title' => 'Tarea ' . $incidence->title,
                    'message' => 'Proyecto: ' . $incidence->project->project_type .
                        ' | Descripción: ' . ($incidence->description ?? 'Sin descripción') .
                        ' | Fecha límite: ' . $incidence->due_date,
                    'read' => false,
                    'link' => '/project-management/projects/kanban/'. $incidence->project_id .'/task-details/'. $incidence->id
                ]);
            }

            Log::info('Incidencia creada', [
                'incidence_id' => $incidence->id,
                'project_id' => $projectId,
                'created_by' => $createdById,
                'type_id' => $data['incidence_type_id'],
            ]);

            DB::commit();

            return $incidence;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear incidencia', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'data' => $data,
            ]);

            throw new IncidenceException(
                'Error al crear la incidencia: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Validar reglas de negocio adicionales
     *
     * @throws IncidenceException
     */
    private function validateBusinessRules(array $data): void
    {
        if ($data['incidence_type_id'] == 1 && ! is_null($data['parent_incidence_id'])) {
            throw new IncidenceException(
                'Una incidencia de tipo Epic no puede tener una incidencia padre',
                422
            );
        }

        if ($data['incidence_type_id'] != 1 && is_null($data['parent_incidence_id'])) {
            throw new IncidenceException(
                'Las incidencias que no son de tipo Epic deben tener una incidencia padre',
                422
            );
        }

        if (! is_null($data['parent_incidence_id'])) {
            $parentIncidence = Incidence::find($data['parent_incidence_id']);

            if (! $parentIncidence) {
                throw new IncidenceException(
                    'La incidencia padre no existe',
                    404
                );
            }

            if ($parentIncidence->project_id != $data['project_id']) {
                throw new IncidenceException(
                    'La incidencia padre debe pertenecer al mismo proyecto',
                    422
                );
            }

        }

    }
}
