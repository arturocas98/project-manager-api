<?php

namespace App\Services\Incidence;
use App\Models\Incidence;
use App\Exceptions\IncidenceException;

class UpdateIncidenceService
{
    // Constantes para los tipos de incidencia
    public const TYPE_EPIC = 1;
    public const TYPE_HISTORY_USER = 2;
    public const TYPE_TASK = 3;
    public const TYPE_BUG = 4;
    public const TYPE_SUBTASK = 5;

    // Mapa de jerarquía: [tipo_hijo => tipo_padre_requerido]
    private const HIERARCHY_RULES = [
        self::TYPE_HISTORY_USER => self::TYPE_EPIC,
        self::TYPE_TASK => self::TYPE_HISTORY_USER,
        self::TYPE_BUG => self::TYPE_TASK,
        self::TYPE_SUBTASK => self::TYPE_TASK,
    ];

    // Tipos que pueden ser raíces (sin padre)
    private const ROOT_TYPES = [
        self::TYPE_EPIC,
    ];

    // Estados permitidos (esto debería venir de la base de datos idealmente)
    private const STATE_OPEN = 1;
    private const STATE_IN_PROGRESS = 2;
    private const STATE_CLOSED = 3;
    private const STATE_REOPENED = 4;

    private const ALLOWED_STATE_TRANSITIONS = [
        self::STATE_OPEN => [self::STATE_IN_PROGRESS, self::STATE_CLOSED],
        self::STATE_IN_PROGRESS => [self::STATE_OPEN, self::STATE_CLOSED],
        self::STATE_CLOSED => [self::STATE_REOPENED],
        self::STATE_REOPENED => [self::STATE_IN_PROGRESS, self::STATE_CLOSED],
    ];

    public function __construct(
        private \App\Actions\App\Incidence\UpdateIncidenceAction $updateIncidenceAction
    ) {}

    /**
     * Actualizar una incidencia existente
     *
     * @param int $incidenceId
     * @param array $data
     * @param int $updatedById
     * @return Incidence
     * @throws IncidenceException
     */
    public function update(int $incidenceId, array $data, int $updatedById): Incidence
    {
        // Obtener la incidencia actual con sus relaciones
        $incidence = $this->getIncidenceWithRelations($incidenceId);

        // Validar reglas de negocio antes de actualizar
        $this->validateUpdate($incidence, $data);

        // Registrar cambios para auditoría
        $oldData = $this->captureOldData($incidence);

        // Ejecutar la actualización
        $updatedIncidence = $this->updateIncidenceAction->execute($incidenceId, $data, $updatedById);

        // Recargar relaciones
        $updatedIncidence->load([
            'incidenceType',
            'incidenceState',
            'parentIncidence',
            'childIncidences'
        ]);

        // Log de auditoría
        $this->logChanges($incidenceId, $updatedById, $oldData, $updatedIncidence);

        return $updatedIncidence;
    }

    /**
     * Validar todas las reglas de negocio para la actualización
     *
     * @param Incidence $incidence
     * @param array $data
     * @throws IncidenceException
     */
    private function validateUpdate(Incidence $incidence, array $data): void
    {
        // 1. Validar que no se intente cambiar el proyecto
        $this->validateProjectNotChanged($incidence, $data);

        // 2. Validar restricciones de jerarquía
        $this->validateHierarchyConstraints($incidence, $data);

        // 3. Validar cambios de tipo
        $this->validateTypeChange($incidence, $data);

        // 4. Validar cambios de padre
        $this->validateParentChange($incidence, $data);

        // 5. Validar cambios de estado
        $this->validateStateChange($incidence, $data);

        // 6. Validar cambios de asignación
        $this->validateAssignmentChange($incidence, $data);

        // 7. Validar campos obligatorios según el tipo
        $this->validateRequiredFields($incidence, $data);
    }

    /**
     * Validar que no se cambie el proyecto
     */
    private function validateProjectNotChanged(Incidence $incidence, array $data): void
    {
        if (isset($data['project_id']) && $data['project_id'] !== $incidence->project_id) {
            throw new IncidenceException(
                'No se puede cambiar una incidencia de proyecto. Cree una nueva incidencia en el proyecto destino.',
                422
            );
        }
    }

    /**
     * Validar restricciones de jerarquía
     */
    private function validateHierarchyConstraints(Incidence $incidence, array $data): void
    {
        $newTypeId = $data['incidence_type_id'] ?? $incidence->incidence_type_id;
        $newParentId = $data['parent_incidence_id'] ?? $incidence->parent_incidence_id;

        // Si no hay cambios en tipo ni padre, no validar jerarquía
        if ($newTypeId === $incidence->incidence_type_id &&
            $newParentId === $incidence->parent_incidence_id) {
            return;
        }

        // Validar la nueva jerarquía
        $this->validateHierarchy(
            $incidence->project_id,
            $newTypeId,
            $newParentId,
            $incidence->id // Excluir la propia incidencia de las validaciones
        );
    }

    /**
     * Validar jerarquía según las reglas de negocio
     */
    private function validateHierarchy(int $projectId, int $typeId, ?int $parentId, ?int $excludeId = null): void
    {
        // Caso 1: Es un tipo raíz (Epic)
        if (in_array($typeId, self::ROOT_TYPES)) {
            if (!is_null($parentId)) {
                $typeName = $this->getTypeName($typeId);
                throw new IncidenceException(
                    "Las incidencias de tipo {$typeName} no pueden tener una incidencia padre",
                    422
                );
            }
            return;
        }

        // Caso 2: No es raíz, debe tener padre
        if (is_null($parentId)) {
            $typeName = $this->getTypeName($typeId);
            throw new IncidenceException(
                "Las incidencias de tipo {$typeName} deben tener una incidencia padre",
                422
            );
        }

        // Verificar que el padre existe y pertenece al proyecto
        $parentIncidence = Incidence::where('id', $parentId)
            ->where('project_id', $projectId)
            ->first();

        if (!$parentIncidence) {
            throw new IncidenceException(
                'La incidencia padre no existe o no pertenece al mismo proyecto',
                404
            );
        }

        // Validar que el tipo del padre sea el requerido
        if (!isset(self::HIERARCHY_RULES[$typeId])) {
            throw new IncidenceException(
                'Tipo de incidencia no válido para jerarquía',
                422
            );
        }

        $requiredParentTypeId = self::HIERARCHY_RULES[$typeId];
        $actualParentTypeId = $parentIncidence->incidence_type_id;

        if ($actualParentTypeId !== $requiredParentTypeId) {
            $childTypeName = $this->getTypeName($typeId);
            $requiredParentName = $this->getTypeName($requiredParentTypeId);
            $actualParentName = $this->getTypeName($actualParentTypeId);

            throw new IncidenceException(
                "Jerarquía inválida: Una incidencia de tipo {$childTypeName} debe tener un padre de tipo {$requiredParentName}, pero se asignó un padre de tipo {$actualParentName}",
                422
            );
        }

        // Validar que no se cree un ciclo (que el padre no sea hijo de esta incidencia)
        if ($excludeId) {
            $this->validateNoCycle($parentId, $excludeId);
        }
    }

    /**
     * Validar que no se cree un ciclo en la jerarquía
     */
    private function validateNoCycle(int $parentId, int $childId): void
    {
        $currentParent = $parentId;
        $visited = [$childId];

        while ($currentParent) {
            if (in_array($currentParent, $visited)) {
                throw new IncidenceException(
                    'La jerarquía crearía un ciclo. Verifique las relaciones padre-hijo.',
                    422
                );
            }

            $visited[] = $currentParent;
            $parent = Incidence::find($currentParent);
            $currentParent = $parent?->parent_incidence_id;
        }
    }

    /**
     * Validar cambio de tipo
     */
    private function validateTypeChange(Incidence $incidence, array $data): void
    {
        if (!isset($data['incidence_type_id']) ||
            $data['incidence_type_id'] === $incidence->incidence_type_id) {
            return;
        }

        // No permitir cambiar tipo si tiene hijos
        if ($incidence->childIncidences()->count() > 0) {
            throw new IncidenceException(
                'No se puede cambiar el tipo de una incidencia que tiene incidencias hijas',
                422
            );
        }

        // Validar que el nuevo tipo sea compatible con la posición actual
        $this->validateTypeCompatibility($incidence, $data['incidence_type_id']);
    }

    /**
     * Validar compatibilidad del nuevo tipo con la posición en el árbol
     */
    private function validateTypeCompatibility(Incidence $incidence, int $newTypeId): void
    {
        // Si tiene padre, validar que el nuevo tipo sea compatible con ese padre
        if ($incidence->parent_incidence_id) {
            try {
                $this->validateHierarchy(
                    $incidence->project_id,
                    $newTypeId,
                    $incidence->parent_incidence_id
                );
            } catch (IncidenceException $e) {
                throw new IncidenceException(
                    'El nuevo tipo no es compatible con la posición actual en el árbol: ' . $e->getMessage(),
                    422
                );
            }
        }
    }

    /**
     * Validar cambio de padre
     */
    private function validateParentChange(Incidence $incidence, array $data): void
    {
        if (!isset($data['parent_incidence_id']) ||
            $data['parent_incidence_id'] === $incidence->parent_incidence_id) {
            return;
        }

        $newParentId = $data['parent_incidence_id'];
        $currentTypeId = $incidence->incidence_type_id;

        // Validar la nueva relación padre-hijo
        $this->validateHierarchy(
            $incidence->project_id,
            $currentTypeId,
            $newParentId,
            $incidence->id
        );

        // Validar que el nuevo padre no sea un hijo (directo o indirecto)
        if ($newParentId) {
            $this->validateNoCycle($newParentId, $incidence->id);
        }
    }

    /**
     * Validar cambio de estado
     */
    private function validateStateChange(Incidence $incidence, array $data): void
    {
        if (!isset($data['incidence_state_id']) ||
            $data['incidence_state_id'] === $incidence->incidence_state_id) {
            return;
        }

        $currentState = $incidence->incidence_state_id;
        $newState = $data['incidence_state_id'];

        // Validar que la transición sea permitida
        if (!isset(self::ALLOWED_STATE_TRANSITIONS[$currentState]) ||
            !in_array($newState, self::ALLOWED_STATE_TRANSITIONS[$currentState])) {

            $currentStateName = $this->getStateName($currentState);
            $newStateName = $this->getStateName($newState);

            throw new IncidenceException(
                "No se puede cambiar el estado de {$currentStateName} a {$newStateName}",
                422
            );
        }

        // Validaciones específicas por estado
        if ($newState === self::STATE_CLOSED) {
            $this->validateCanClose($incidence);
        }
    }

    /**
     * Validar que se pueda cerrar la incidencia
     */
    private function validateCanClose(Incidence $incidence): void
    {
        // Verificar que todas las incidencias hijas estén cerradas
        $openChildren = $incidence->childIncidences()
            ->where('incidence_state_id', '!=', self::STATE_CLOSED)
            ->count();

        if ($openChildren > 0) {
            throw new IncidenceException(
                'No se puede cerrar una incidencia que tiene incidencias hijas abiertas',
                422
            );
        }
    }

    /**
     * Validar cambio de asignación
     */
    private function validateAssignmentChange(Incidence $incidence, array $data): void
    {
        if (!isset($data['assigned_user_id'])) {
            return;
        }

        // Si se está desasignando, siempre permitido
        if ($data['assigned_user_id'] === null) {
            return;
        }

        // Validar que el usuario asignado exista y tenga acceso al proyecto
        // Esta validación dependerá de tu lógica de negocio
    }

    /**
     * Validar campos obligatorios según el tipo
     */
    private function validateRequiredFields(Incidence $incidence, array $data): void
    {
        $typeId = $data['incidence_type_id'] ?? $incidence->incidence_type_id;

        switch ($typeId) {
            case self::TYPE_BUG:
                // Los bugs podrían requerir campos adicionales
                if (isset($data['description']) && empty($data['description'])) {
                    throw new IncidenceException(
                        'Los bugs requieren una descripción detallada',
                        422
                    );
                }
                break;

            case self::TYPE_TASK:
                // Las tasks podrían requerir fecha límite
                if (isset($data['date']) && empty($data['date'])) {
                    throw new IncidenceException(
                        'Las tareas requieren una fecha asignada',
                        422
                    );
                }
                break;
        }
    }

    /**
     * Obtener incidencia con relaciones necesarias
     */
    private function getIncidenceWithRelations(int $incidenceId): Incidence
    {
        $incidence = Incidence::with([
            'incidenceType',
            'incidenceState',
            'parentIncidence',
            'childIncidences'
        ])->find($incidenceId);

        if (!$incidence) {
            throw new IncidenceException('Incidencia no encontrada', 404);
        }

        return $incidence;
    }

    /**
     * Capturar datos antiguos para auditoría
     */
    private function captureOldData(Incidence $incidence): array
    {
        return [
            'title' => $incidence->title,
            'description' => $incidence->description,
            'incidence_type_id' => $incidence->incidence_type_id,
            'incidence_state_id' => $incidence->incidence_state_id,
            'parent_incidence_id' => $incidence->parent_incidence_id,
            'assigned_user_id' => $incidence->assigned_user_id,
            'date' => $incidence->date,
        ];
    }

    /**
     * Registrar cambios en log
     */
    private function logChanges(int $incidenceId, int $updatedById, array $oldData, Incidence $newIncidence): void
    {
        $changes = [];

        foreach ($oldData as $field => $oldValue) {
            $newValue = $newIncidence->$field;
            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        if (!empty($changes)) {
            Log::info('Incidencia actualizada', [
                'incidence_id' => $incidenceId,
                'updated_by' => $updatedById,
                'changes' => $changes
            ]);
        }
    }

    /**
     * Obtener nombre del tipo
     */
    private function getTypeName(int $typeId): string
    {
        return match($typeId) {
            self::TYPE_EPIC => 'Epic',
            self::TYPE_HISTORY_USER => 'History User',
            self::TYPE_TASK => 'Task',
            self::TYPE_BUG => 'Bug',
            self::TYPE_SUBTASK => 'Subtask',
            default => 'Desconocido'
        };
    }

    /**
     * Obtener nombre del estado
     */
    private function getStateName(int $stateId): string
    {
        return match($stateId) {
            self::STATE_OPEN => 'Abierto',
            self::STATE_IN_PROGRESS => 'En Progreso',
            self::STATE_CLOSED => 'Cerrado',
            self::STATE_REOPENED => 'Reabierto',
            default => 'Desconocido'
        };
    }

    /**
     * Validar que se pueda reabrir una incidencia
     */
    public function validateCanReopen(Incidence $incidence): void
    {
        if ($incidence->incidence_state_id !== self::STATE_CLOSED) {
            throw new IncidenceException(
                'Solo se pueden reabrir incidencias cerradas',
                422
            );
        }
    }

    /**
     * Reabrir una incidencia (método helper)
     */
    public function reopen(int $incidenceId, int $updatedById): Incidence
    {
        $incidence = $this->getIncidenceWithRelations($incidenceId);

        $this->validateCanReopen($incidence);

        return $this->update($incidenceId, [
            'incidence_state_id' => self::STATE_REOPENED
        ], $updatedById);
    }
}