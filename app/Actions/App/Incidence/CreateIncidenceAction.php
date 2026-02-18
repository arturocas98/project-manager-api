<?php

namespace App\Actions\App\Incidence;

use App\Models\Incidence;
use App\Exceptions\IncidenceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateIncidenceAction
{
    /**
     * Create a new incidence
     *
     * @param int $projectId
     * @param array $data
     * @param int $createdById
     * @return Incidence
     * @throws IncidenceException
     */
    public function execute(int $projectId, array $data, int $createdById): Incidence
    {
        try {
            DB::beginTransaction();

            // Preparar datos base
            $incidenceData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'date' => $data['date'] ?? now()->toDateString(),
                'priority' => $data['priority'] ?? 'media',
                'project_id' => $projectId,
                'incidence_type_id' => $data['incidence_type_id'],
                'incidence_state_id' => 1, // Estado predeterminado: 1 (ej: "Abierto" o "Nuevo")
                'created_by_id' => $createdById,
                'assigned_user_id' => null, // Siempre nulo al crear
                'parent_incidence_id' => $data['parent_incidence_id'] ?? null,
            ];

            // Validación adicional de negocio
            $this->validateBusinessRules($incidenceData);

            // Crear la incidencia
            $incidence = Incidence::create($incidenceData);

            // Log de la creación
            Log::info('Incidencia creada', [
                'incidence_id' => $incidence->id,
                'project_id' => $projectId,
                'created_by' => $createdById,
                'type_id' => $data['incidence_type_id']
            ]);

            DB::commit();

            return $incidence;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear incidencia', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'data' => $data
            ]);

            throw new IncidenceException(
                'Error al crear la incidencia: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Validar reglas de negocio adicionales
     *
     * @param array $data
     * @throws IncidenceException
     */
    private function validateBusinessRules(array $data): void
    {
        // Si es Epic (tipo 1), no puede tener padre
        if ($data['incidence_type_id'] == 1 && !is_null($data['parent_incidence_id'])) {
            throw new IncidenceException(
                'Una incidencia de tipo Epic no puede tener una incidencia padre',
                422
            );
        }

        // Si no es Epic, debe tener padre
        if ($data['incidence_type_id'] != 1 && is_null($data['parent_incidence_id'])) {
            throw new IncidenceException(
                'Las incidencias que no son de tipo Epic deben tener una incidencia padre',
                422
            );
        }

        // Validar que la incidencia padre exista y pertenezca al mismo proyecto
        if (!is_null($data['parent_incidence_id'])) {
            $parentIncidence = Incidence::find($data['parent_incidence_id']);

            if (!$parentIncidence) {
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

            // Validar que la incidencia padre no sea del mismo tipo que la hija?
            // Esta validación podría ser opcional según tus reglas de negocio
        }
    }
}