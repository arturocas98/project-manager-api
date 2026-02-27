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
    // Constantes para los tipos de incidencia
    public const TYPE_EPIC = 1;
    public const TYPE_HISTORY_USER = 2;
    public const TYPE_TASK = 3;
    public const TYPE_BUG = 4;
    public const TYPE_SUBTASK = 5;

    // Mapa de jerarquía: [tipo_hijo => tipo_padre_requerido]
    private const HIERARCHY_RULES = [
        self::TYPE_HISTORY_USER => self::TYPE_EPIC,           // history_user -> epic
        self::TYPE_TASK => self::TYPE_HISTORY_USER,           // task -> history_user
        self::TYPE_BUG => self::TYPE_TASK,                     // bug -> task
        self::TYPE_SUBTASK => self::TYPE_TASK,                 // subtask -> task (mismo nivel que bug)
    ];

    // Tipos que pueden ser raíces (sin padre)
    private const ROOT_TYPES = [
        self::TYPE_EPIC,
    ];

    // Roles permitidos para crear incidencias
    private const ALLOWED_CREATOR_ROLES = [
        'project manager',
        'administrators',
        'supervisor',
        'external contributor'
    ];

    public function __construct(
        private IncidenceQuery $incidenceQuery,
        private CreateIncidenceAction $createIncidenceAction
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
        // Validar que el creador tenga un rol permitido
        $this->validateCreatorRole($projectId, $createdById);

        // Validar jerarquía antes de crear
        $this->validateIncidenceHierarchy($projectId, $data);

        // Validar fechas
        $this->validateDates($data);

        // Validar usuario asignado si se proporciona
        if (isset($data['assigned_user_id']) && $data['assigned_user_id']) {
            $this->validateAssignedUser($projectId, $data['assigned_user_id'], $data['incidence_type_id']);
        } else {
            // Si no se asigna usuario, validar si es permitido según el tipo
            $this->validateNullableAssignedUser($data['incidence_type_id']);
        }

        return $this->createIncidenceAction->execute($projectId, $data, $createdById);
    }

    /**
     * Validar que el usuario creador tenga un rol permitido
     */
    private function validateCreatorRole(int $projectId, int $creatorId): void
    {
        // Buscar el rol del creador en el proyecto
        $creatorProject = ProjectUser::where('user_id', $creatorId)
            ->whereHas('role', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with('role')
            ->first();

        // Si no tiene rol en el proyecto
        if (!$creatorProject || !$creatorProject->role) {
            throw new IncidenceException(
                "No tienes un rol asignado en este proyecto",
                403
            );
        }

        $creatorRoleType = strtolower($creatorProject->role->type);

        // Verificar si el rol está permitido
        if (!in_array($creatorRoleType, self::ALLOWED_CREATOR_ROLES)) {
            throw new IncidenceException(
                "No tienes permisos para crear incidencias en este proyecto. Tu rol '{$creatorProject->role->type}' no está autorizado. Roles permitidos: " . implode(', ', self::ALLOWED_CREATOR_ROLES),
                403
            );
        }
    }

    /**
     * Validar que el usuario asignado tenga un rol permitido según el tipo de incidencia
     */
    private function validateAssignedUser(int $projectId, int $assignedUserId, int $incidenceTypeId): void
    {
        // Buscar el rol del usuario en el proyecto
        $projectUser = ProjectUser::where('user_id', $assignedUserId)
            ->whereHas('role', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with('role')
            ->first();

        // Si no tiene rol en el proyecto
        if (!$projectUser || !$projectUser->role) {
            throw new IncidenceException(
                "El usuario seleccionado no tiene un rol asignado en este proyecto",
                422
            );
        }

        $userRoleType = strtolower($projectUser->role->type);

        // Validaciones específicas según el tipo de incidencia
        if (in_array($incidenceTypeId, [self::TYPE_EPIC, self::TYPE_HISTORY_USER])) {
            // Epic (1) o History (2) - solo project manager o supervisor
            $allowedRolesForEpicAndHistory = ['project manager', 'supervisor', 'administrators'];

            if (!in_array($userRoleType, $allowedRolesForEpicAndHistory)) {
                throw new IncidenceException(
                    "Las incidencias de tipo Epic o History solo pueden ser asignadas a usuarios con rol Project Manager o Supervisor. El usuario seleccionado tiene rol: {$projectUser->role->type}",
                    422
                );
            }
        }
        else if (in_array($incidenceTypeId, [self::TYPE_TASK, self::TYPE_BUG, self::TYPE_SUBTASK])) {
            // Task (3), Bug (4), Subtask (5) - cualquier rol excepto client, guest, owner
            $forbiddenRoles = ['client', 'guest', 'owner'];

            if (in_array($userRoleType, $forbiddenRoles)) {
                throw new IncidenceException(
                    "No se puede asignar una tarea a un usuario con rol {$projectUser->role->type}",
                    422
                );
            }
        }
    }

    /**
     * Validar si es permitido que no haya usuario asignado según el tipo de incidencia
     */
    private function validateNullableAssignedUser(int $incidenceTypeId): void
    {
        // Para Epic y History, podrías requerir que siempre tengan asignación
        if (in_array($incidenceTypeId, [self::TYPE_EPIC, self::TYPE_HISTORY_USER])) {
            // Si quieres que sea obligatorio, descomenta la siguiente línea:
            // throw new IncidenceException("Las incidencias de tipo Epic o History deben tener un usuario asignado", 422);
        }

        // Para Task, Bug y Subtask, puede ser null (sin asignar)
        // No se requiere validación adicional
    }

    private function validateDates(array $data): void
    {
        // Si ambas fechas están presentes, validar que start_date <= due_date
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

        // Validar que due_date no sea demasiado lejano (opcional)
        if (isset($data['due_date'])) {
            $dueDate = Carbon::parse($data['due_date']);
            $maxDueDate = now()->addMonths(6); // Máximo 6 meses

            if ($dueDate->gt($maxDueDate)) {
                throw new IncidenceException(
                    'La fecha de vencimiento no puede ser superior a 6 meses',
                    422
                );
            }
        }

        // Validaciones específicas por tipo
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

        // Caso 1: Es un tipo raíz (Epic)
        if (in_array($incidenceTypeId, self::ROOT_TYPES)) {
            if (!is_null($parentId)) {
                throw new IncidenceException(
                    "Las incidencias de tipo Epic no pueden tener una incidencia padre",
                    422
                );
            }
            return; // ✅ Válido: Epic sin padre
        }

        // Caso 2: No es raíz, debe tener padre
        if (is_null($parentId)) {
            $typeName = $this->getTypeName($incidenceTypeId);
            throw new IncidenceException(
                "Las incidencias de tipo {$typeName} deben tener una incidencia padre",
                422
            );
        }

        // Verificar que el padre existe y pertenece al proyecto
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

        // Validar que el tipo del padre sea el requerido según la jerarquía
        $this->validateParentType($incidenceTypeId, $parentIncidence);
    }

    /**
     * Validar que el tipo del padre sea el correcto según la jerarquía
     */
    private function validateParentType(int $childTypeId, Incidence $parentIncidence): void
    {
        // Verificar si el tipo hijo tiene una regla de jerarquía definida
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

        // Validaciones adicionales específicas
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
                // Bugs y Subtasks pueden estar al mismo nivel (ambos hijos de Task)
                // No hay validaciones adicionales específicas
                break;

            case self::TYPE_TASK:
                // Las Tasks deben verificar que su padre (History User) no tenga ya muchas tareas
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
        // Verificar si tiene hijos
        $childrenCount = $incidence->childIncidences()->count();

        if ($childrenCount > 0) {
            throw new IncidenceException(
                "No se puede eliminar una incidencia que tiene {$childrenCount} incidencias hijas",
                422
            );
        }

        // Verificar si es padre de algún bug o subtask (dependiendo del tipo)
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
     * Obtener el árbol completo de incidencias para un proyecto
     */
    public function getIncidenceTree(int $projectId): Collection
    {
        // Obtener todas las Epics (raíces)
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

        if (!$hasAccess) {
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