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
        $this->validatePermissions($project);
        $this->validateData($data);

        // TRANSACCIÓN
        return DB::transaction(function () use ($project, $data) {

            // EJECUTAR ACCIÓN
            $updatedProject = $this->updateProject->execute($project, $data);

            // VERIFICAR RESULTADO
            if (!$updatedProject) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Error al actualizar el proyecto',
                        'reason' => 'La acción de actualización no devolvió el proyecto actualizado',
                        'project_id' => $project->id
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
                'createdBy'
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
                    'deleted_at' => $project->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }
    }

    /**
     * Validar permisos de administrador
     */
    private function validatePermissions(Project $project): void
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no autenticado',
                    'reason' => 'Se requiere un usuario autenticado para esta acción'
                ]),
                401
            );
        }

        $isAdmin = $project->roles()
            ->where('type', 'Administrators')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (!$isAdmin) {
            // Obtener el rol del usuario para mejor mensaje
            $userRole = $project->roles()
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->first();

            $roleName = $userRole?->type ?? 'Sin rol asignado';

            throw new ProjectException(
                json_encode([
                    'error' => 'Permiso denegado',
                    'reason' => 'Se requiere rol de Administrador',
                    'user_id' => $userId,
                    'user_role' => $roleName,
                    'project_id' => $project->id,
                    'required_role' => 'Administrators'
                ]),
                403
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
                    'suggestion' => 'Envía al menos uno de los campos permitidos'
                ]),
                400
            );
        }

        $allowedFields = ['name', 'description', 'key'];
        $receivedFields = array_keys($data);
        $validFields = array_intersect($receivedFields, $allowedFields);

        if (empty($validFields)) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Campos inválidos',
                    'reason' => 'Ninguno de los campos enviados es válido',
                    'received_fields' => $receivedFields,
                    'allowed_fields' => $allowedFields,
                    'suggestion' => 'Usa: ' . implode(', ', $allowedFields)
                ]),
                400
            );
        }

        // Validar que al menos un campo tenga valor
        $hasValue = false;
        $emptyFields = [];

        foreach ($validFields as $field) {
            if (isset($data[$field]) && !is_null($data[$field]) && $data[$field] !== '') {
                $hasValue = true;
            } else {
                $emptyFields[] = $field;
            }
        }

        if (!$hasValue) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Valores vacíos',
                    'reason' => 'Los campos enviados están vacíos',
                    'empty_fields' => $emptyFields,
                    'suggestion' => 'Los campos no pueden estar vacíos'
                ]),
                400
            );
        }

        // Validaciones específicas por campo
        if (isset($data['key'])) {
            if (!preg_match('/^[A-Z0-9]{2,10}$/', $data['key'])) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Formato de clave inválido',
                        'reason' => 'La clave debe tener 2-10 caracteres alfanuméricos en mayúsculas',
                        'received' => $data['key'],
                        'examples' => ['WEB', 'API', 'CRM23', 'TOC']
                    ]),
                    400
                );
            }

            // Verificar que la clave no exista en otro proyecto
            $existingProject = Project::where('key', $data['key'])
                ->where('id', '!=', $project->id ?? 0)
                ->first();

            if ($existingProject) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Clave duplicada',
                        'reason' => 'Ya existe otro proyecto con esta clave',
                        'key' => $data['key'],
                        'existing_project' => [
                            'id' => $existingProject->id,
                            'name' => $existingProject->name
                        ]
                    ]),
                    400
                );
            }
        }

        if (isset($data['name']) && strlen($data['name']) < 3) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Nombre muy corto',
                    'reason' => 'El nombre debe tener al menos 3 caracteres',
                    'received' => $data['name']
                ]),
                400
            );
        }
    }
}