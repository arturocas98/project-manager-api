<?php


namespace App\Services\Incidence;

use App\Exceptions\ProjectException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Project;
use Illuminate\Support\Collection;

class IncidenceService
{
    public function getProjectIncidences(int $projectId, array $filters = []): Collection
    {
        $query = (new IncidenceQuery())
            ->byProject($projectId)
            ->withDefaultRelations();

        // Aplicar filtros de fecha si existen
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

        return $query->orderByLatest()->get();
    }

    public function getIncidencesDueSoon(int $projectId, int $days = 7): Collection
    {
        $startDate = now();
        $endDate = now()->addDays($days);

        return (new IncidenceQuery())
            ->byProject($projectId)
            ->byDateRange(null, $endDate)
            ->where('due_date', '>=', $startDate)
            ->where('incidence_state_id', '!=', 3) // Excluir cerradas
            ->withDefaultRelations()
            ->orderByDueDate()
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
}