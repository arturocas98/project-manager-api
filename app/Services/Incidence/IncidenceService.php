<?php

namespace App\Services\Incidence;

use App\Exceptions\ProjectException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Support\Collection;

class IncidenceService
{
    /**
     * Obtener incidencias del proyecto según el rol del usuario
     */
    public function getProjectIncidences(int $projectId, array $filters = []): Collection
    {
        $user = auth()->user();
        $userId = $user->id;

        // Obtener el rol del usuario en el proyecto
        $roleData = $this->getUserRoleInProject($projectId, $userId);

        if (!$roleData) {
            throw new ProjectException('No tienes un rol asignado en este proyecto', 403);
        }

        // Construir query base y obtener TODAS las incidencias del proyecto
        $query = (new IncidenceQuery())->byProject($projectId);

        // Cargar relaciones necesarias
        $query->withDefaultRelations();

        // Aplicar filtros de fecha
        if (isset($filters['start_date']) || isset($filters['due_date'])) {
            $query->byDateRange(
                $filters['start_date'] ?? null,
                $filters['due_date'] ?? null
            );
        }

        // Filtrar vencidas
        if (isset($filters['overdue']) && $filters['overdue']) {
            $query->overdue();
        }

        // Ordenar por fecha de vencimiento
        if (isset($filters['sort_by_due_date'])) {
            $query->orderByDueDate($filters['sort_by_due_date']);
        }

        // Obtener TODAS las incidencias
        $allIncidences = $query->orderByLatest()->get();

        // 🔥 FILTRAR SEGÚN EL ROL (sobre la colección)
        return $this->filterIncidencesByRole($allIncidences, $roleData, $userId);
    }

    /**
     * 🔥 NUEVO: Filtra las incidencias según el rol del usuario
     */
    private function filterIncidencesByRole(Collection $incidences, object $roleData, int $userId): Collection
    {
        // Si es administrators o project manager, devolver TODAS sin filtrar
        if (in_array($roleData->role_type, ['administrators', 'project manager'])) {
            return $incidences;
        }

        // Para cualquier otro rol, filtrar SOLO las asignadas a él
        return $incidences->filter(function ($incidence) use ($userId) {
            return $incidence->assigned_to == $userId;
        })->values(); // values() reindexa la colección
    }

    /**
     * Obtener incidencias próximas a vencer
     */
    public function getIncidencesDueSoon(int $projectId, int $days = 7): Collection
    {
        $user = auth()->user();
        $userId = $user->id;

        // Obtener rol
        $roleData = $this->getUserRoleInProject($projectId, $userId);

        if (!$roleData) {
            return collect([]);
        }

        $startDate = now();
        $endDate = now()->addDays($days);

        $query = (new IncidenceQuery())
            ->byProject($projectId)
            ->byDateRange(null, $endDate)
            ->where('due_date', '>=', $startDate)
            ->where('incidence_state_id', '!=', 3); // Excluir cerradas

        // Obtener TODAS las incidencias próximas a vencer
        $allIncidences = $query->withDefaultRelations()
            ->orderByDueDate()
            ->get();

        // 🔥 APLICAR MISMO FILTRO POR ROL (sobre la colección)
        return $this->filterIncidencesByRole($allIncidences, $roleData, $userId);
    }

    /**
     * Obtiene el rol del usuario en el proyecto
     */
    private function getUserRoleInProject(int $projectId, int $userId): ?object
    {
        // Buscar al usuario en el proyecto
        $projectUser = ProjectUser::with('role')
            ->where('user_id', $userId)
            ->whereHas('role', function($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->first();

        if (!$projectUser || !$projectUser->role) {
            return null;
        }

        return (object) [
            'role_id' => $projectUser->role->id,
            'role_type' => $projectUser->role->type,
            'role_name' => $projectUser->role->type,
        ];
    }

    /**
     * Validar acceso al proyecto
     */
    public function validateProjectAccess(Project $project): void
    {
        $userId = auth()->id();

        $hasAccess = $project->roles()
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $hasAccess) {
            throw new ProjectException(
                'Acceso denegado: No tienes acceso a este proyecto',
                403
            );
        }
    }

    /**
     * Verificar si un usuario tiene acceso total
     */
    public function hasFullAccess(int $projectId, ?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        $roleData = $this->getUserRoleInProject($projectId, $userId);

        if (!$roleData) {
            return false;
        }

        return in_array($roleData->role_type, ['administrators', 'project manager']);
    }
}