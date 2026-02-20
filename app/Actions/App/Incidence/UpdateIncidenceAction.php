<?php
namespace App\Actions\App\Incidence;
use App\Exceptions\IncidenceException;
use App\Models\Incidence;

class UpdateIncidenceAction
{
    /**
     * Actualizar una incidencia existente
     *
     * @param int $incidenceId
     * @param array $data
     * @param int $updatedById
     * @return Incidence
     * @throws IncidenceException
     */
    public function execute(int $incidenceId, array $data, int $updatedById): Incidence
    {
        try {
            \DB::beginTransaction();

            // Obtener la incidencia con lock para evitar condiciones de carrera
            $incidence = Incidence::where('id', $incidenceId)
                ->lockForUpdate()
                ->first();

            if (!$incidence) {
                throw new IncidenceException(
                    "Incidencia no encontrada",
                    404
                );
            }

            // Preparar datos para actualizar (solo campos permitidos)
            $updateData = $this->prepareUpdateData($data);

            // Validar que hay datos para actualizar
            if (empty($updateData)) {
                throw new IncidenceException(
                    "No se proporcionaron datos válidos para actualizar",
                    422
                );
            }

            // Guardar el estado anterior para referencia
            $oldStateId = $incidence->incidence_state_id;
            $oldAssignedUserId = $incidence->assigned_user_id;

            // Actualizar la incidencia
            $incidence->update($updateData);

            // Si se actualizó el estado, validar que sea una transición válida
            if (isset($data['incidence_state_id']) && $data['incidence_state_id'] !== $oldStateId) {
                $this->validateStateTransition($oldStateId, $data['incidence_state_id']);
            }

            // Si se actualizó la asignación, validar que el usuario existe
            if (isset($data['assigned_user_id']) && $data['assigned_user_id'] !== $oldAssignedUserId) {
                if ($data['assigned_user_id'] !== null) {
                    $this->validateUserExists($data['assigned_user_id']);
                }
            }

            \DB::commit();

            return $incidence;

        } catch (IncidenceException $e) {
            \DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            \DB::rollBack();

            throw new IncidenceException(
                "Error al actualizar la incidencia: " . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Preparar los datos para la actualización (solo campos permitidos)
     *
     * @param array $data
     * @return array
     * @throws IncidenceException
     */
    private function prepareUpdateData(array $data): array
    {
        $allowedFields = [
            'title',
            'description',
            'incidence_type_id',
            'incidence_priority_id',
            'incidence_state_id',
            'parent_incidence_id',
            'assigned_user_id',
            'date',
        ];

        $updateData = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                // Validar que los campos requeridos no vayan vacíos
                if (in_array($field, ['title', 'incidence_type_id']) && empty($data[$field])) {
                    throw new IncidenceException(
                        "El campo {$field} no puede estar vacío",
                        422
                    );
                }

                $updateData[$field] = $data[$field];
            }
        }

        return $updateData;
    }

    /**
     * Validar que la transición de estado sea válida
     *
     * @param int $oldStateId
     * @param int $newStateId
     * @throws IncidenceException
     */
    private function validateStateTransition(int $oldStateId, int $newStateId): void
    {
        $allowedTransitions = [
            1 => [2, 3], // Abierto -> En progreso, Cerrado
            2 => [1, 3], // En progreso -> Abierto, Cerrado
            3 => [1],    // Cerrado -> Abierto (reabrir)
        ];

        if (!isset($allowedTransitions[$oldStateId]) ||
            !in_array($newStateId, $allowedTransitions[$oldStateId])) {

            throw new IncidenceException(
                "Transición de estado no permitida",
                422
            );
        }
    }

    /**
     * Validar que el usuario existe
     *
     * @param int $userId
     * @throws IncidenceException
     */
    private function validateUserExists(int $userId): void
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            throw new IncidenceException(
                "El usuario asignado no existe",
                404
            );
        }
    }

    /**
     * Actualizar múltiples incidencias a la vez (batch update)
     *
     * @param array $incidenceIds
     * @param array $data
     * @param int $updatedById
     * @return int Número de incidencias actualizadas
     * @throws IncidenceException
     */
    public function executeBatch(array $incidenceIds, array $data, int $updatedById): int
    {
        try {
            \DB::beginTransaction();

            // Preparar datos comunes para todas
            $updateData = $this->prepareUpdateData($data);

            if (empty($updateData)) {
                throw new IncidenceException(
                    "No se proporcionaron datos válidos para actualizar",
                    422
                );
            }

            // Validar que los IDs existen
            $existingCount = Incidence::whereIn('id', $incidenceIds)->count();

            if ($existingCount !== count($incidenceIds)) {
                throw new IncidenceException(
                    "Una o más incidencias no existen",
                    404
                );
            }

            // Actualizar todas las incidencias
            $count = Incidence::whereIn('id', $incidenceIds)
                ->update($updateData);

            \DB::commit();

            return $count;

        } catch (IncidenceException $e) {
            \DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            \DB::rollBack();

            throw new IncidenceException(
                "Error al actualizar múltiples incidencias: " . $e->getMessage(),
                500
            );
        }
    }
}