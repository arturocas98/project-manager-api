<?php

namespace App\Services\Incidence;
use App\Models\Incidence;
use App\Exceptions\IncidenceException;
use App\Models\Project;
use Carbon\Carbon;

class UpdateIncidenceService
{
    // Constantes para los tipos de incidencia
    public const TYPE_EPIC = 1;
    public const TYPE_HISTORY_USER = 2;
    public const TYPE_TASK = 3;
    public const TYPE_BUG = 4;
    public const TYPE_SUBTASK = 5;

    // Constantes para los estados
    private const STATE_ASSIGNED = 1;       // Asignado
    private const STATE_IN_PROGRESS = 2;    // Ejecutando
    private const STATE_SUSPENDED = 3;      // Suspendido
    private const STATE_FINISHED = 4;       // Terminada
    private const STATE_FINISHED_LATE = 5;  // Terminada (fuera de plazo)
    private const STATE_REVIEW = 6;         // En Revisión
    private const STATE_COMPLETED = 7;      // Finalizada

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

    // Mapa de transiciones con roles permitidos
    private const STATE_FLOW_RULES = [
        self::STATE_ASSIGNED => [
            self::STATE_IN_PROGRESS => ['DEV'], // Solo el colaborador asignado
            self::STATE_SUSPENDED => ['ADM', 'LDR'], // Líder/Admin por inconvenientes
        ],
        self::STATE_IN_PROGRESS => [
            self::STATE_SUSPENDED => ['ADM', 'LDR'], // Líder/Admin por inconvenientes
            self::STATE_FINISHED => ['DEV'], // Colaborador cuando completa
            self::STATE_FINISHED_LATE => ['DEV'], // Considerado igual a terminada para el flujo
        ],
        self::STATE_SUSPENDED => [
            self::STATE_IN_PROGRESS => ['ADM', 'LDR'], // Líder/Admin cuando se resuelve
        ],
        self::STATE_FINISHED => [
            self::STATE_REVIEW => ['TST', 'ADM', 'LDR'], // Tester inicia validación
        ],
        self::STATE_FINISHED_LATE => [
            self::STATE_REVIEW => ['TST', 'ADM', 'LDR'], // Igual que terminada
        ],
        self::STATE_REVIEW => [
            self::STATE_IN_PROGRESS => ['TST', 'ADM', 'LDR'], // Tester rechaza (requiere corrección)
            self::STATE_COMPLETED => ['TST', 'ADM', 'LDR'], // Tester aprueba
        ],
        self::STATE_COMPLETED => [], // Estado final, no más transiciones
    ];

    // Mapeo de códigos de rol a nombres (para mensajes)
    private const ROLE_CODE_TO_NAME = [
        'ADM' => 'Administrador',
        'LDR' => 'Líder',
        'DEV' => 'Desarrollador',
        'TST' => 'Tester',
        'DOC' => 'Documentador',
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
        $this->validateUpdate($incidence, $data, $updatedById);
        $this->validateDateUpdate($incidence, $data);

        // Ejecutar la actualización
        $updatedIncidence = $this->updateIncidenceAction->execute($incidenceId, $data, $updatedById);

        // Recargar relaciones
        $updatedIncidence->load([
            'incidenceType',
            'incidenceState',
            'parentIncidence',
            'childIncidences'
        ]);

        return $updatedIncidence;
    }

    /**
     * Validar todas las reglas de negocio para la actualización
     *
     * @param Incidence $incidence
     * @param array $data
     * @param int $updatedById
     * @throws IncidenceException
     */
    private function validateUpdate(Incidence $incidence, array $data, int $updatedById): void
    {
        // 1. Validar que no se intente cambiar el proyecto
        $this->validateProjectNotChanged($incidence, $data);

        // 2. Validar restricciones de jerarquía
        $this->validateHierarchyConstraints($incidence, $data);

        // 3. Validar cambios de tipo
        $this->validateTypeChange($incidence, $data);

        // 4. Validar cambios de padre
        $this->validateParentChange($incidence, $data);

        // 5. Validar cambios de estado (CON ROLES)
        $this->validateStateChange($incidence, $data, $updatedById);

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
     * Validar cambio de estado considerando roles
     */
    private function validateStateChange(Incidence $incidence, array $data, int $updatedById): void
    {
        if (!isset($data['incidence_state_id']) ||
            $data['incidence_state_id'] === $incidence->incidence_state_id) {
            return;
        }

        $currentState = $incidence->incidence_state_id;
        $newState = $data['incidence_state_id'];

        // Validar que la transición exista en las reglas
        $this->validateStateTransitionExists($currentState, $newState);

        // Validar que el usuario tenga el rol adecuado para esta transición
        $this->validateUserCanPerformTransition($incidence, $currentState, $newState, $updatedById);

        // Validaciones específicas por estado
        if ($newState === self::STATE_REVIEW) {
            $this->validateCanMoveToReview($incidence);
        }

        if ($newState === self::STATE_COMPLETED) {
            $this->validateCanComplete($incidence);
        }

        if ($newState === self::STATE_IN_PROGRESS && $currentState === self::STATE_REVIEW) {
            $this->validateReopenFromReview($incidence);
        }
    }

    /**
     * Validar que la transición de estado exista en las reglas
     */
    private function validateStateTransitionExists(int $currentState, int $newState): void
    {
        if (!isset(self::STATE_FLOW_RULES[$currentState])) {
            $currentStateName = $this->getStateName($currentState);
            throw new IncidenceException(
                "El estado {$currentStateName} no tiene transiciones definidas",
                422
            );
        }

        if (!isset(self::STATE_FLOW_RULES[$currentState][$newState])) {
            $currentStateName = $this->getStateName($currentState);
            $newStateName = $this->getStateName($newState);

            throw new IncidenceException(
                "No se puede cambiar de {$currentStateName} a {$newStateName}: " .
                "transición no permitida en el flujo de trabajo",
                422
            );
        }
    }

    /**
     * Validar que el usuario tenga el rol adecuado para la transición
     */
    private function validateUserCanPerformTransition(
        Incidence $incidence,
        int $currentState,
        int $newState,
        int $userId
    ): void {
        // Obtener los roles permitidos para esta transición
        $allowedRoleCodes = self::STATE_FLOW_RULES[$currentState][$newState];

        // Si no hay restricción de roles, permitir a todos
        if (empty($allowedRoleCodes)) {
            return;
        }

        // Obtener los roles del usuario en el proyecto
        $userRoles = $this->getUserRolesInProject($userId, $incidence->project_id);

        if (empty($userRoles)) {
            throw new IncidenceException(
                'El usuario no tiene roles asignados en este proyecto',
                403
            );
        }

        // Verificar si alguno de los roles del usuario está permitido
        $hasAllowedRole = collect($userRoles)
            ->pluck('code')
            ->intersect($allowedRoleCodes)
            ->isNotEmpty();

        if (!$hasAllowedRole) {
            $currentStateName = $this->getStateName($currentState);
            $newStateName = $this->getStateName($newState);
            $allowedRolesNames = $this->getRoleNamesFromCodes($allowedRoleCodes);

            throw new IncidenceException(
                "No tienes permisos para cambiar de {$currentStateName} a {$newStateName}. " .
                "Esta acción solo puede ser realizada por: " . implode(', ', $allowedRolesNames),
                403
            );
        }

        // Validaciones adicionales específicas por rol
        $this->validateSpecificRoleConstraints($incidence, $newState, $userId, $userRoles);
    }

    /**
     * Obtener los roles del usuario en el proyecto
     */
    private function getUserRolesInProject(int $userId, int $projectId): array
    {
        $project = Project::with(['roles' => function($query) use ($userId) {
            $query->whereHas('users', fn($q) => $q->where('user_id', $userId));
        }])->find($projectId);

        if (!$project) {
            return [];
        }

        return $project->roles->map(function($role) {
            return [
                'id' => $role->id,
                'code' => $role->code,
                'type' => $role->type,
            ];
        })->toArray();
    }

    /**
     * Validaciones específicas según el rol y contexto
     */
    private function validateSpecificRoleConstraints(
        Incidence $incidence,
        int $newState,
        int $userId,
        array $userRoles
    ): void {
        // Si el nuevo estado es IN_PROGRESS (Ejecutando) y viene de ASIGNADO
        if ($newState === self::STATE_IN_PROGRESS &&
            $incidence->incidence_state_id === self::STATE_ASSIGNED) {

            // Verificar que el usuario sea el asignado a la tarea
            if ($incidence->assigned_user_id !== $userId) {
                // A menos que sea Admin o Líder
                $isAdminOrLeader = collect($userRoles)
                    ->whereIn('code', ['ADM', 'LDR'])
                    ->isNotEmpty();

                if (!$isAdminOrLeader) {
                    throw new IncidenceException(
                        'Solo el usuario asignado a la tarea puede cambiar el estado a Ejecutando',
                        403
                    );
                }
            }
        }

        // Si el nuevo estado es FINISHED (Terminada)
        if ($newState === self::STATE_FINISHED) {
            // Verificar que el usuario sea el asignado (para DEV)
            $isDev = collect($userRoles)->where('code', 'DEV')->isNotEmpty();

            if ($isDev && $incidence->assigned_user_id !== $userId) {
                throw new IncidenceException(
                    'Un desarrollador solo puede marcar como terminada una tarea asignada a él',
                    403
                );
            }
        }
    }

    /**
     * Validar que se pueda mover a revisión
     */
    private function validateCanMoveToReview(Incidence $incidence): void
    {
        // Verificar que la tarea esté en estado "Terminada" o "Terminada (fuera de plazo)"
        if (!in_array($incidence->incidence_state_id, [self::STATE_FINISHED, self::STATE_FINISHED_LATE])) {
            throw new IncidenceException(
                'Solo las tareas en estado "Terminada" pueden pasar a revisión',
                422
            );
        }

        // Verificar que tenga descripción de lo realizado
        if (empty($incidence->description)) {
            throw new IncidenceException(
                'Debe proporcionar una descripción del trabajo realizado antes de pasar a revisión',
                422
            );
        }
    }

    /**
     * Validar que se pueda completar la tarea
     */
    private function validateCanComplete(Incidence $incidence): void
    {
        // Verificar que todas las subtareas estén completadas si existen
        if ($incidence->childIncidences()->count() > 0) {
            $openChildren = $incidence->childIncidences()
                ->whereNotIn('incidence_state_id', [self::STATE_COMPLETED, self::STATE_FINISHED, self::STATE_FINISHED_LATE])
                ->count();

            if ($openChildren > 0) {
                throw new IncidenceException(
                    'No se puede finalizar una tarea que tiene subtareas pendientes',
                    422
                );
            }
        }
    }

    /**
     * Validar reapertura desde revisión
     */
    private function validateReopenFromReview(Incidence $incidence): void
    {
        // El tester debe proporcionar un motivo de rechazo (validar en request aparte)
        // Esta validación se hace a nivel de controller/request
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
        $userHasAccess = Project::find($incidence->project_id)
            ?->hasUserAccess($data['assigned_user_id']);

        if (!$userHasAccess) {
            throw new IncidenceException(
                'El usuario asignado no tiene acceso al proyecto',
                422
            );
        }
    }

    /**
     * Validar campos obligatorios según el tipo
     */
    private function validateRequiredFields(Incidence $incidence, array $data): void
    {
        $typeId = $data['incidence_type_id'] ?? $incidence->incidence_type_id;

        switch ($typeId) {
            case self::TYPE_BUG:
                // Los bugs requieren descripción
                $description = $data['description'] ?? $incidence->description;
                if (empty($description)) {
                    throw new IncidenceException(
                        'Los bugs requieren una descripción detallada',
                        422
                    );
                }
                break;
        }
    }

    /**
     * Validar actualización de fechas
     */
    private function validateDateUpdate(Incidence $incidence, array $data): void
    {
        $startDate = $data['start_date'] ?? $incidence->start_date;
        $dueDate = $data['due_date'] ?? $incidence->due_date;

        // Si se están actualizando las fechas
        if (isset($data['start_date']) || isset($data['due_date'])) {

            // Validar que start_date <= due_date si ambos están presentes
            if ($startDate && $dueDate) {
                $startCarbon = Carbon::parse($startDate);
                $dueCarbon = Carbon::parse($dueDate);

                if ($startCarbon->gt($dueCarbon)) {
                    throw new IncidenceException(
                        'La fecha de inicio no puede ser posterior a la fecha de vencimiento',
                        422
                    );
                }
            }

            // Validaciones específicas por estado
            if ($incidence->incidence_state_id === self::STATE_COMPLETED) {
                if (isset($data['due_date']) || isset($data['start_date'])) {
                    throw new IncidenceException(
                        'No se pueden modificar las fechas de una incidencia finalizada',
                        422
                    );
                }
            }

            // Validar que due_date no sea en el pasado si la incidencia está en progreso
            if ($incidence->incidence_state_id === self::STATE_IN_PROGRESS && isset($data['due_date'])) {
                $newDueDate = Carbon::parse($data['due_date']);

                if ($newDueDate->lt(now())) {
                    throw new IncidenceException(
                        'No se puede establecer una fecha de vencimiento en el pasado para una incidencia en progreso',
                        422
                    );
                }
            }
        }

        // Validaciones específicas por tipo al actualizar
        $newTypeId = $data['incidence_type_id'] ?? $incidence->incidence_type_id;

        if ($newTypeId === self::TYPE_TASK) {
            $finalDueDate = $data['due_date'] ?? $incidence->due_date;

            if (!$finalDueDate) {
                throw new IncidenceException(
                    'Las tareas deben tener una fecha de vencimiento',
                    422
                );
            }
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
            'start_date' => $incidence->start_date,
            'due_date' => $incidence->due_date,
        ];
    }

    /**
     * Obtener nombre del tipo
     */
    private function getTypeName(int $typeId): string
    {
        return match($typeId) {
            self::TYPE_EPIC => 'Epic',
            self::TYPE_HISTORY_USER => 'Historia de Usuario',
            self::TYPE_TASK => 'Tarea',
            self::TYPE_BUG => 'Bug',
            self::TYPE_SUBTASK => 'Subtarea',
            default => 'Desconocido'
        };
    }

    /**
     * Obtener nombre del estado
     */
    private function getStateName(int $stateId): string
    {
        return match($stateId) {
            self::STATE_ASSIGNED => 'Asignado',
            self::STATE_IN_PROGRESS => 'Ejecutando',
            self::STATE_SUSPENDED => 'Suspendido',
            self::STATE_FINISHED => 'Terminada',
            self::STATE_FINISHED_LATE => 'Terminada (fuera de plazo)',
            self::STATE_REVIEW => 'En Revisión',
            self::STATE_COMPLETED => 'Finalizada',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener nombres de roles a partir de códigos
     */
    private function getRoleNamesFromCodes(array $roleCodes): array
    {
        return array_map(function($code) {
            return self::ROLE_CODE_TO_NAME[$code] ?? $code;
        }, $roleCodes);
    }
}