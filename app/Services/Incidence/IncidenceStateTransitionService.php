<?php

namespace App\Services\Incidence;

use App\Exceptions\IncidenceException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Incidence;
use App\Models\IncidenceState;
use App\Models\ProjectUser;
use App\Models\User;


class IncidenceStateTransitionService
{
    // Estados usando códigos
    public const STATE_ASSIGNED = 'ASG';
    public const STATE_RUNNING = 'EJE';
    public const STATE_SUSPENDED = 'SUS';
    public const STATE_COMPLETED = 'TER';
    public const STATE_REVIEW = 'REV';
    public const STATE_FINISHED = 'FIN';

    // Mapa de transiciones permitidas: [estado_actual => [estado_siguiente => condiciones]]
    private const TRANSITIONS = [
        // (nueva) → Asignado
        null => [
            self::STATE_ASSIGNED => [
                'allowed_roles' => ['LDR', 'ADM'], // Líder o Administrador
                'requires_assignment' => true,      // Debe tener un usuario asignado
                'condition' => 'Al crear y asignar la tarea'
            ]
        ],

        // Asignado → Ejecutando
        self::STATE_ASSIGNED => [
            self::STATE_RUNNING => [
                'allowed_roles' => ['DEV', 'TST', 'DOC'], // Colaborador asignado
                'requires_assigned_user' => true,          // Solo el usuario asignado puede iniciar
                'condition' => 'Cuando inicia el trabajo'
            ]
        ],

        // Ejecutando → Suspendido
        self::STATE_RUNNING => [
            self::STATE_SUSPENDED => [
                'allowed_roles' => ['LDR', 'ADM'], // Líder o Administrador
                'condition' => 'Cuando hay un inconveniente'
            ]
        ],

        // Suspendido → Ejecutando
        self::STATE_SUSPENDED => [
            self::STATE_RUNNING => [
                'allowed_roles' => ['LDR', 'ADM'], // Líder o Administrador
                'condition' => 'Cuando se resuelve el inconveniente'
            ]
        ],

        // Ejecutando → Terminada
        self::STATE_RUNNING => [
            self::STATE_COMPLETED => [
                'allowed_roles' => ['DEV', 'TST', 'DOC'], // Colaborador asignado
                'requires_assigned_user' => true,          // Solo el usuario asignado puede terminar
                'condition' => 'Cuando completa la tarea'
            ]
        ],

        // Terminada → En Revisión
        self::STATE_COMPLETED => [
            self::STATE_REVIEW => [
                'allowed_roles' => ['TST'], // Solo Tester
                'condition' => 'Cuando inicia la validación'
            ]
        ],

        // En Revisión → Ejecutando (cuando no aprueba)
        self::STATE_REVIEW => [
            self::STATE_RUNNING => [
                'allowed_roles' => ['TST'], // Tester (quien revisa)
                'condition' => 'Si no aprueba (requiere corrección)',
                'requires_rejection_reason' => true // Debe haber un motivo de rechazo
            ]
        ],

        // En Revisión → Finalizada
        self::STATE_REVIEW => [
            self::STATE_FINISHED => [
                'allowed_roles' => ['TST'], // Tester (quien revisa)
                'condition' => 'Cuando aprueba la tarea'
            ]
        ],
    ];

    public function __construct(
        private CreateIndiceService $createIndiceService
    ) {}

    /**
     * Validar asignación inicial al crear incidencia
     */
    public function validateInitialAssignment(int $projectId, array $data): void
    {
        if (!isset($data['assigned_user_id'])) {
            return;
        }

        // Crear incidencia temporal para validación
        $tempIncidence = new Incidence([
            'project_id' => $projectId,
            'incidence_state_id' => null,
            'assigned_user_id' => $data['assigned_user_id'],
            'incidence_type_id' => $data['incidence_type_id'],
        ]);

        $this->validateTransition(
            $tempIncidence,
            null,
            self::STATE_ASSIGNED,
            auth()->user()
        );
    }

    /**
     * Validar cambio de estado al actualizar
     */
    public function validateStateChange(int $incidenceId, array $data): void
    {
        if (!isset($data['incidence_state_id'])) {
            return;
        }

        $incidence = Incidence::findOrFail($incidenceId);
        $newState = IncidenceState::findOrFail($data['incidence_state_id']);

        $this->validateTransition(
            $incidence,
            $incidence->incidenceState?->code,
            $newState->code,
            auth()->user(),
            $data
        );

        // Validar reasignación si aplica
        if (isset($data['assigned_user_id']) &&
            $data['assigned_user_id'] != $incidence->assigned_user_id) {
            $this->validateReassignment($incidence, $data['assigned_user_id']);
        }
    }

    /**
     * Validar y ejecutar una transición de estado
     */
    public function transition(Incidence $incidence, string $newStateCode, ?array $data = []): Incidence
    {
        $currentStateCode = $incidence->incidenceState?->code;
        $user = auth()->user();

        // Validar que la transición sea permitida
        $this->validateTransition($incidence, $currentStateCode, $newStateCode, $user, $data);

        // Ejecutar acciones pre-transición
        $this->executePreTransitionActions($incidence, $currentStateCode, $newStateCode, $data);

        // Actualizar el estado
        $newState = IncidenceState::where('code', $newStateCode)->firstOrFail();
        $incidence->update(['incidence_state_id' => $newState->id]);

        // Registrar el cambio de estado
        $this->logStateChange($incidence, $currentStateCode, $newStateCode, $user, $data);

        // Ejecutar acciones post-transición
        $this->executePostTransitionActions($incidence, $currentStateCode, $newStateCode, $data);

        return $incidence->fresh();
    }

    /**
     * Validar si una transición es permitida
     */
    public function validateTransition(Incidence $incidence, ?string $currentState, string $newState, $user, array $data = []): void
    {
        // Verificar si existe la transición desde el estado actual
        if (!isset(self::TRANSITIONS[$currentState][$newState])) {
            $currentName = $currentState ? $this->getStateName($currentState) : 'Nueva';
            $newName = $this->getStateName($newState);

            throw new IncidenceException(
                "No se permite la transición de {$currentName} a {$newName}",
                422
            );
        }

        $transition = self::TRANSITIONS[$currentState][$newState];

        // Validar rol del usuario
        $this->validateUserRole($incidence, $user, $transition['allowed_roles']);

        // Validar que sea el usuario asignado cuando se requiere
        if (($transition['requires_assigned_user'] ?? false) &&
            $incidence->assigned_user_id !== $user->id) {
            throw new IncidenceException(
                "Solo el usuario asignado puede realizar esta acción",
                403
            );
        }

        // Validar que tenga asignación cuando se requiere
        if (($transition['requires_assignment'] ?? false) && !$incidence->assigned_user_id) {
            throw new IncidenceException(
                "La incidencia debe tener un usuario asignado",
                422
            );
        }

        // Validar que tenga motivo de rechazo cuando se requiere
        if (($transition['requires_rejection_reason'] ?? false) && empty($data['rejection_reason'])) {
            throw new IncidenceException(
                "Debe proporcionar un motivo de rechazo",
                422
            );
        }

        // Validaciones específicas por transición
        $this->validateSpecificTransitions($incidence, $currentState, $newState, $data);
    }

    /**
     * Validar reasignación de usuario
     */
    private function validateReassignment(Incidence $incidence, int $newUserId): void
    {
        if ($incidence->incidenceState?->code === self::STATE_RUNNING) {
            throw new IncidenceException(
                "No se puede reasignar una incidencia en estado 'Ejecutando'. Debe suspenderse primero.",
                422
            );
        }

        // Validar que el nuevo asignado tenga el rol adecuado
        $this->createIndiceService->validateAssignedUser(
            $incidence->project_id,
            $newUserId,
            $incidence->incidence_type_id
        );
    }

    /**
     * Validar que el usuario tenga el rol permitido
     */
    private function validateUserRole(Incidence $incidence, $user, array $allowedCodes): void
    {
        // Obtener el rol del usuario en el proyecto
        $userRole = ProjectUser::where('user_id', $user->id)
            ->whereHas('role', function ($query) use ($incidence) {
                $query->where('project_id', $incidence->project_id);
            })
            ->with('role.permissionScheme.scheme')
            ->first();

        if (!$userRole || !$userRole->role) {
            throw new IncidenceException(
                "No tienes un rol asignado en este proyecto",
                403
            );
        }

        $userRoleCode = $userRole->role->permissionScheme->scheme->code ?? null;

        if (!in_array($userRoleCode, $allowedCodes)) {
            $allowedNames = implode(', ', array_map([$this, 'getRoleName'], $allowedCodes));
            throw new IncidenceException(
                "Se requiere uno de los siguientes roles: {$allowedNames}",
                403
            );
        }
    }

    /**
     * Validaciones específicas por tipo de transición
     */
    private function validateSpecificTransitions(Incidence $incidence, ?string $currentState, string $newState, array $data): void
    {
        // De En Revisión a Ejecutando (rechazo)
        if ($currentState === self::STATE_REVIEW && $newState === self::STATE_RUNNING) {
            // Validar que la incidencia tenga un tester asignado
            if (!$incidence->tester_user_id && empty($data['tester_user_id'])) {
                throw new IncidenceException(
                    "Se requiere asignar un tester para la revisión",
                    422
                );
            }
        }

        // De Terminada a En Revisión
        if ($currentState === self::STATE_COMPLETED && $newState === self::STATE_REVIEW) {
            // Asignar automáticamente al tester si no está definido
            if (empty($data['tester_user_id']) && !$incidence->tester_user_id) {
                // Buscar un tester disponible en el proyecto
                $tester = $this->findAvailableTester($incidence->project_id);
                if ($tester) {
                    $data['tester_user_id'] = $tester->id;
                }
            }
        }
    }

    /**
     * Ejecutar acciones antes de la transición
     */
    private function executePreTransitionActions(Incidence $incidence, ?string $currentState, string $newState, array &$data): void
    {
        // Cuando se asigna por primera vez (null → ASG)
        if ($currentState === null && $newState === self::STATE_ASSIGNED) {
            // Registrar quién asignó
            $data['assigned_by'] = auth()->id();
            $data['assigned_at'] = now();
        }

        // Cuando se inicia el trabajo (ASG → EJE)
        if ($currentState === self::STATE_ASSIGNED && $newState === self::STATE_RUNNING) {
            $data['started_at'] = now();
        }

        // Cuando se completa (EJE → TER)
        if ($currentState === self::STATE_RUNNING && $newState === self::STATE_COMPLETED) {
            $data['completed_at'] = now();
        }

        // Cuando se rechaza (REV → EJE)
        if ($currentState === self::STATE_REVIEW && $newState === self::STATE_RUNNING) {
            $data['rejected_at'] = now();
            $data['rejected_by'] = auth()->id();
        }

        // Cuando se finaliza (REV → FIN)
        if ($currentState === self::STATE_REVIEW && $newState === self::STATE_FINISHED) {
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->id();
        }
    }

    /**
     * Ejecutar acciones después de la transición
     */
    private function executePostTransitionActions(Incidence $incidence, ?string $currentState, string $newState, array $data): void
    {
        // Notificaciones según el nuevo estado
        switch ($newState) {
            case self::STATE_ASSIGNED:
                // Notificar al usuario asignado
                $this->notifyAssignedUser($incidence);
                break;

            case self::STATE_RUNNING:
                // Notificar que empezó
                $this->notifyWorkStarted($incidence);
                break;

            case self::STATE_COMPLETED:
                // Notificar que está lista para revisión
                $this->notifyReadyForReview($incidence);
                break;

            case self::STATE_REVIEW:
                // Notificar al tester
                $this->notifyTester($incidence);
                break;

            case self::STATE_FINISHED:
                // Notificar que está finalizada
                $this->notifyFinished($incidence);
                break;
        }
    }

    /**
     * Registrar el cambio de estado
     */
    private function logStateChange(Incidence $incidence, ?string $oldState, string $newState, $user, array $data): void
    {
        throw new IncidenceException(json_encode([
            'message' => 'Cambio de estado de incidencia',
            'incidence_id' => $incidence->id,
            'old_state' => $oldState,
            'new_state' => $newState,
            'user_id' => $user->id,
            'user_role' => $this->getUserRoleCode($user, $incidence->project_id),
            'metadata' => $data
        ]), 400);
    }

    /**
     * Obtener todas las transiciones posibles desde un estado
     */
    public function getAvailableTransitions(?string $currentState, Incidence $incidence, $user): array
    {
        $available = [];

        if (!isset(self::TRANSITIONS[$currentState])) {
            return $available;
        }

        foreach (self::TRANSITIONS[$currentState] as $nextState => $transition) {
            try {
                $this->validateTransition($incidence, $currentState, $nextState, $user, []);
                $available[] = [
                    'code' => $nextState,
                    'name' => $this->getStateName($nextState),
                    'condition' => $transition['condition']
                ];
            } catch (\Exception $e) {
                // No disponible, continuar
                continue;
            }
        }

        return $available;
    }

    /**
     * Buscar un tester disponible en el proyecto
     */
    private function findAvailableTester(int $projectId): ?User
    {
        // Buscar usuarios con rol TST en el proyecto
        $testerRole = ProjectUser::whereHas('role', function ($query) use ($projectId) {
            $query->where('project_id', $projectId)
                ->whereHas('permissionScheme.scheme', function ($q) {
                    $q->where('code', 'TST');
                });
        })->with('user')->first();

        return $testerRole?->user;
    }

    /**
     * Obtener nombre del estado por su código
     */
    public function getStateName(string $code): string
    {
        return match ($code) {
            self::STATE_ASSIGNED => 'Asignado',
            self::STATE_RUNNING => 'Ejecutando',
            self::STATE_SUSPENDED => 'Suspendido',
            self::STATE_COMPLETED => 'Terminada',
            self::STATE_REVIEW => 'En Revisión',
            self::STATE_FINISHED => 'Finalizada',
            default => $code
        };
    }

    /**
     * Obtener nombre del rol por su código
     */
    private function getRoleName(string $code): string
    {
        return match ($code) {
            'ADM' => 'Administrador',
            'LDR' => 'Líder',
            'DEV' => 'Desarrollador',
            'TST' => 'Tester',
            'DOC' => 'Documentador',
            default => $code
        };
    }

    /**
     * Obtener código del rol del usuario en el proyecto
     */
    private function getUserRoleCode($user, int $projectId): ?string
    {
        $userRole = ProjectUser::where('user_id', $user->id)
            ->whereHas('role', fn($q) => $q->where('project_id', $projectId))
            ->with('role.permissionScheme.scheme')
            ->first();

        return $userRole?->role?->permissionScheme?->scheme?->code;
    }

    // Métodos de notificación (implementar según tu sistema)
    private function notifyAssignedUser(Incidence $incidence): void
    {
        // TODO: Implementar notificación al usuario asignado
    }

    private function notifyWorkStarted(Incidence $incidence): void
    {
        // TODO: Implementar notificación de inicio de trabajo
    }

    private function notifyReadyForReview(Incidence $incidence): void
    {
        // TODO: Implementar notificación de lista para revisión
    }

    private function notifyTester(Incidence $incidence): void
    {
        // TODO: Implementar notificación al tester
    }

    private function notifyFinished(Incidence $incidence): void
    {
        // TODO: Implementar notificación de finalización
    }
}