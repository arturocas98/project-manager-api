<?php

namespace App\Services\Project;

use App\Actions\App\Project\UpdateProjectAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectUpdateService
{
    public function __construct(
        private UpdateProjectAction $updateProject
    ) {}

    /**
     * Actualizar proyecto con verificación de permisos
     */
    public function update(Project $project, array $data): Project
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validateData($data);

        // TRANSACCIÓN
        return DB::transaction(function () use ($project, $data) {

            // EJECUTAR ACCIÓN
            $updatedProject = $this->updateProject->execute($project, $data);

            // VERIFICAR RESULTADO
            if (! $updatedProject) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Error al actualizar el proyecto',
                        'reason' => 'La acción de actualización no devolvió el proyecto actualizado',
                        'project_id' => $project->id,
                    ]),
                    500
                );
            }

            // CARGAR RELACIONES NECESARIAS
            $updatedProject->load([
                'roles' => function ($q) {
                    $q->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
                        ->with(['permissionScheme.scheme.permissions']);
                },
                'createdBy',
            ]);

            return $updatedProject;
        });
    }

    /**
     * Validar que el proyecto existe y no está eliminado
     */
    private function validateProject(Project $project): void
    {
        if ($project->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto no disponible',
                    'reason' => 'El proyecto ha sido eliminado',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString(),
                ]),
                400
            );
        }
    }

    /**
     * Validar que hay datos para actualizar
     */
    private function validateData(array $data): void
    {
        if (empty($data)) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Datos insuficientes',
                    'reason' => 'No se recibieron datos para actualizar',
                    'suggestion' => 'Envía al menos uno de los campos permitidos',
                ]),
                400
            );
        }

        $allowedFields = ['project_type', 'description', 'ContractNo'];
        $receivedFields = array_keys($data);
        $validFields = array_intersect($receivedFields, $allowedFields);

        if (empty($validFields)) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Campos inválidos',
                    'reason' => 'Ninguno de los campos enviados es válido',
                    'received_fields' => $receivedFields,
                    'allowed_fields' => $allowedFields,
                    'suggestion' => 'Usa: ' . implode(', ', $allowedFields),
                ]),
                400
            );
        }

        // Validar que al menos un campo tenga valor
        $hasValue = false;
        $emptyFields = [];

        foreach ($validFields as $field) {
            if (isset($data[$field]) && ! is_null($data[$field]) && $data[$field] !== '') {
                $hasValue = true;
            } else {
                $emptyFields[] = $field;
            }
        }

        if (! $hasValue) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Valores vacíos',
                    'reason' => 'Los campos enviados están vacíos',
                    'empty_fields' => $emptyFields,
                    'suggestion' => 'Los campos no pueden estar vacíos',
                ]),
                400
            );
        }
    }
}
