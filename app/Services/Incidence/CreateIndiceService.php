<?php

namespace App\Services\Incidence;

use App\Actions\App\Incidence\CreateIncidenceAction;
use App\Exceptions\IncidenceException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Project;
use App\Models\Incidence;
use App\Exceptions\ProjectException;
use App\Models\ProjectUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CreateIndiceService
{
    public const TYPE_EPIC = 1;
    public const TYPE_HISTORY_USER = 2;
    public const TYPE_TASK = 3;
    public const TYPE_BUG = 4;
    public const TYPE_SUBTASK = 5;

    private const HIERARCHY_RULES = [
        self::TYPE_HISTORY_USER => self::TYPE_EPIC,
        self::TYPE_TASK => self::TYPE_HISTORY_USER,
        self::TYPE_BUG => self::TYPE_TASK,
        self::TYPE_SUBTASK => self::TYPE_TASK,
    ];

    private const ROOT_TYPES = [
        self::TYPE_EPIC,
    ];

    public function __construct(
        private IncidenceQuery $incidenceQuery,
        private CreateIncidenceAction $createIncidenceAction,
        private IncidenceStateTransitionService $stateTransitionService
    ) {}

    public function getProjectIncidences(int $projectId): Collection
    {
        return $this->incidenceQuery
            ->byProject($projectId)
            ->withDefaultRelations()
            ->orderByLatest()
            ->get();
    }

    public function createIncidence(int $projectId, array $data, int $createdById): Incidence
    {
        if (isset($data['assigned_user_id'])) {
            $this->stateTransitionService->validateInitialAssignment($projectId, $data);
        }

        $this->validateIncidenceHierarchy($projectId, $data);

        $this->validateDates($data);

        if (isset($data['assigned_user_id']) && $data['assigned_user_id']) {
            $this->validateAssignedUser($projectId, $data['assigned_user_id'], $data['incidence_type_id']);
        }

        return $this->createIncidenceAction->execute($projectId, $data, $createdById);
    }

    /**
     * Validar que el usuario asignado tenga un rol permitido según el tipo de incidencia
     */
    public function validateAssignedUser(int $projectId, int $assignedUserId, int $incidenceTypeId): void
    {
        $projectUser = ProjectUser::where('user_id', $assignedUserId)
            ->whereHas('role', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with('role.permissionScheme.scheme')
            ->first();

        if (!$projectUser || !$projectUser->role) {
            throw new IncidenceException(
                "El usuario seleccionado no tiene un rol asignado en este proyecto",
                422
            );
        }

        $userRoleCode = $projectUser->role->permissionScheme->scheme->code ?? null;

        if (in_array($incidenceTypeId, [self::TYPE_EPIC, self::TYPE_HISTORY_USER])) {
            $allowedCodesForEpicAndHistory = ['LDR', 'ADM'];

            if (!in_array($userRoleCode, $allowedCodesForEpicAndHistory)) {
                throw new IncidenceException(
                    "Las incidencias de tipo Epic o History solo pueden ser asignadas a usuarios con rol de Líder o Administrador. El usuario seleccionado tiene rol: {$projectUser->role->type}",
                    422
                );
            }
        } else if (in_array($incidenceTypeId, [self::TYPE_TASK, self::TYPE_BUG, self::TYPE_SUBTASK])) {
            $allowedCodesForTasks = ['DEV', 'TST', 'DOC'];

            if (!in_array($userRoleCode, $allowedCodesForTasks)) {
                throw new IncidenceException(
                    "Las tareas solo pueden ser asignadas a desarrolladores, testers o documentadores. El usuario seleccionado tiene rol: {$projectUser->role->type}",
                    422
                );
            }
        }
    }

    private function validateDates(array $data): void
    {
        if (isset($data['start_date']) && isset($data['due_date'])) {
            $startDate = Carbon::parse($data['start_date']);
            $dueDate = Carbon::parse($data['due_date']);

            if ($startDate->gt($dueDate)) {
                throw new IncidenceException(
                    'La fecha de inicio no puede ser posterior a la fecha de vencimiento',
                    422
                );
            }
        }

        if (isset($data['due_date'])) {
            $dueDate = Carbon::parse($data['due_date']);
            $maxDueDate = now()->addMonths(6);

            if ($dueDate->gt($maxDueDate)) {
                throw new IncidenceException(
                    'La fecha de vencimiento no puede ser superior a 6 meses',
                    422
                );
            }
        }

        $typeId = $data['incidence_type_id'] ?? null;

        if ($typeId === self::TYPE_TASK && !isset($data['due_date'])) {
            throw new IncidenceException(
                'Las tareas requieren una fecha de vencimiento',
                422
            );
        }
    }

    /**
     * Validar la jerarquía de la incidencia según las reglas de negocio
     */
    private function validateIncidenceHierarchy(int $projectId, array $data): void
    {
        $incidenceTypeId = $data['incidence_type_id'];
        $parentId = $data['parent_incidence_id'] ?? null;

        if (in_array($incidenceTypeId, self::ROOT_TYPES)) {
            if (!is_null($parentId)) {
                throw new IncidenceException(
                    "Las incidencias de tipo Epic no pueden tener una incidencia padre",
                    422
                );
            }
            return;
        }

        if (is_null($parentId)) {
            $typeName = $this->getTypeName($incidenceTypeId);
            throw new IncidenceException(
                "Las incidencias de tipo {$typeName} deben tener una incidencia padre",
                422
            );
        }

        $parentIncidence = Incidence::find($parentId);
        if (!$parentIncidence) {
            throw new IncidenceException(
                "La incidencia padre no existe",
                404
            );
        }

        if ($parentIncidence->project_id !== $projectId) {
            throw new IncidenceException(
                "La incidencia padre debe pertenecer al mismo proyecto",
                422
            );
        }

        $this->validateParentType($incidenceTypeId, $parentIncidence);
    }

    /**
     * Validar que el tipo del padre sea el correcto según la jerarquía
     */
    private function validateParentType(int $childTypeId, Incidence $parentIncidence): void
    {
        if (!isset(self::HIERARCHY_RULES[$childTypeId])) {
            $childTypeName = $this->getTypeName($childTypeId);
            throw new IncidenceException(
                "Tipo de incidencia no reconocido: {$childTypeName}",
                422
            );
        }

        $requiredParentTypeId = self::HIERARCHY_RULES[$childTypeId];
        $actualParentTypeId = $parentIncidence->incidence_type_id;

        if ($actualParentTypeId !== $requiredParentTypeId) {
            $childTypeName = $this->getTypeName($childTypeId);
            $actualParentTypeName = $this->getTypeName($actualParentTypeId);
            $requiredParentTypeName = $this->getTypeName($requiredParentTypeId);

            throw new IncidenceException(
                "Jerarquía inválida: Una incidencia de tipo {$childTypeName} debe tener un padre de tipo {$requiredParentTypeName}, pero se asignó un padre de tipo {$actualParentTypeName}",
                422
            );
        }

        $this->validateSpecificRules($childTypeId, $parentIncidence);
    }

    /**
     * Validaciones específicas por tipo
     */
    private function validateSpecificRules(int $childTypeId, Incidence $parentIncidence): void
    {
        switch ($childTypeId) {
            case self::TYPE_BUG:
            case self::TYPE_SUBTASK:
                break;

            case self::TYPE_TASK:
                $taskCount = Incidence::where('parent_incidence_id', $parentIncidence->id)
                    ->whereIn('incidence_type_id', [self::TYPE_TASK, self::TYPE_BUG, self::TYPE_SUBTASK])
                    ->count();

                if ($taskCount > 10) {
                    throw new IncidenceException(
                        "El History User ya tiene muchas tareas hijas (máximo 10)",
                        422
                    );
                }
                break;
        }
    }

    /**
     * Validar que se pueda eliminar una incidencia respetando la jerarquía
     */
    public function validateCanDeleteIncidence(Incidence $incidence): void
    {
        $childrenCount = $incidence->childIncidences()->count();

        if ($childrenCount > 0) {
            throw new IncidenceException(
                "No se puede eliminar una incidencia que tiene {$childrenCount} incidencias hijas",
                422
            );
        }

        if (in_array($incidence->incidence_type_id, [self::TYPE_EPIC, self::TYPE_HISTORY_USER])) {
            $dependentCount = Incidence::where('parent_incidence_id', $incidence->id)->count();
            if ($dependentCount > 0) {
                throw new IncidenceException(
                    "No se puede eliminar una incidencia que tiene {$dependentCount} incidencias dependientes",
                    422
                );
            }
        }
    }

    /**
     * Obtener el nombre del tipo por su ID
     */
    private function getTypeName(int $typeId): string
    {
        return match ($typeId) {
            self::TYPE_EPIC => 'Epic',
            self::TYPE_HISTORY_USER => 'History User',
            self::TYPE_TASK => 'Task',
            self::TYPE_BUG => 'Bug',
            self::TYPE_SUBTASK => 'Subtask',
            default => 'Desconocido'
        };
    }

    /**
     * Obtener el árbol completo de incidencias para un proyecto
     */
    public function getIncidenceTree(int $projectId): Collection
    {
        return $this->incidenceQuery
            ->byProject($projectId)
            ->byType(self::TYPE_EPIC)
            ->with(['childIncidences' => function ($query) {
                $query->with(['childIncidences' => function ($q) {
                    $q->with('childIncidences');
                }]);
            }])
            ->withDefaultRelations()
            ->get();
    }

    public function validateProjectAccess(Project $project): void
    {
        $userId = auth()->id();

        $hasAccess = $project->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $hasAccess) {
            throw new ProjectException(
                'Acceso denegado: No tienes acceso a este proyecto',
                403
            );
        }
    }

    public function loadIncidenceRelations(Incidence $incidence): Incidence
    {
        return $incidence->load([
            'incidenceType',
            'incidenceState',
            'createdBy:id,name,email',
            'assignedUser:id,name,email',
            'parentIncidence:id,title,incidence_type_id',
            'parentIncidence.incidenceType',
            'childIncidences'
        ]);
    }
}