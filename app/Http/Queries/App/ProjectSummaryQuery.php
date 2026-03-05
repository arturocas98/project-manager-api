<?php

namespace App\Http\Queries\App;

use App\Models\Incidence;
use App\Models\IncidencePriority;
use App\Models\IncidenceState;
use App\Models\IncidenceType;
use App\Services\Incidence\IncidenceStateTransitionService;
use Carbon\Carbon;
use DB;

class ProjectSummaryQuery
{
    /**
     * Get base statistics for KPIs usando códigos
     */
    public function getBaseStatistics(int $projectId): array
    {
        // Obtener IDs de estados por código
        $inProgressStateId = IncidenceState::where('code', IncidenceStateTransitionService::STATE_RUNNING)->value('id');
        $completedStateId = IncidenceState::where('code', IncidenceStateTransitionService::STATE_COMPLETED)->value('id');
        $reviewStateId = IncidenceState::where('code', IncidenceStateTransitionService::STATE_REVIEW)->value('id');
        $finishedStateId = IncidenceState::where('code', IncidenceStateTransitionService::STATE_FINISHED)->value('id');

        // Obtener IDs de prioridades por código
        $criticalPriorityId = IncidencePriority::where('code', 'CRT')->value('id');
        $highPriorityId = IncidencePriority::where('code', 'ALT')->value('id');

        // Definir rangos de fechas
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Estadísticas del mes actual
        $currentStats = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->selectRaw('
                COALESCE(COUNT(*), 0) as total,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as review,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as finished,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as critical,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as high
            ', [$inProgressStateId, $completedStateId, $reviewStateId, $finishedStateId, $criticalPriorityId, $highPriorityId])
            ->first();

        // Estadísticas del mes anterior
        $previousStats = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->selectRaw('
                COALESCE(COUNT(*), 0) as total,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as review,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as finished,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as critical,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as high
            ', [$inProgressStateId, $completedStateId, $reviewStateId, $finishedStateId, $criticalPriorityId, $highPriorityId])
            ->first();

        return [
            'total' => [
                'value' => (int) ($currentStats->total ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->total ?? 0),
                    (int) ($previousStats->total ?? 0)
                ),
            ],
            'in_progress' => [
                'value' => (int) ($currentStats->in_progress ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->in_progress ?? 0),
                    (int) ($previousStats->in_progress ?? 0)
                ),
            ],
            'completed' => [
                'value' => (int) ($currentStats->completed ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->completed ?? 0),
                    (int) ($previousStats->completed ?? 0)
                ),
            ],
            'review' => [
                'value' => (int) ($currentStats->review ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->review ?? 0),
                    (int) ($previousStats->review ?? 0)
                ),
            ],
            'finished' => [
                'value' => (int) ($currentStats->finished ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->finished ?? 0),
                    (int) ($previousStats->finished ?? 0)
                ),
            ],
            'critical' => [
                'value' => (int) ($currentStats->critical ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->critical ?? 0),
                    (int) ($previousStats->critical ?? 0)
                ),
            ],
            'high' => [
                'value' => (int) ($currentStats->high ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($currentStats->high ?? 0),
                    (int) ($previousStats->high ?? 0)
                ),
            ],
        ];
    }

    /**
     * Calculate percentage comparison between current and previous values
     */
    private function calculateComparison(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $percentage = (($current - $previous) / $previous) * 100;
        $formatted = number_format(abs($percentage), 1) . '%';

        return $percentage >= 0 ? "+{$formatted}" : "-{$formatted}";
    }

    /**
     * Get distribution by state usando códigos
     */
    public function getStateDistribution(int $projectId): array
    {
        $result = Incidence::where('project_id', $projectId)
            ->join('incidence_states', 'incidences.incidence_state_id', '=', 'incidence_states.id')
            ->select('incidence_states.code', DB::raw('COALESCE(COUNT(*), 0) as total'))
            ->groupBy('incidence_states.code', 'incidence_states.id')
            ->orderBy('incidence_states.id')
            ->pluck('total', 'code')
            ->toArray();

        return array_map('intval', $result);
    }

    /**
     * Get distribution by priority usando códigos
     */
    public function getPriorityDistribution(int $projectId): array
    {
        $result = Incidence::where('project_id', $projectId)
            ->join('incidence_priorities', 'incidences.incidence_priority_id', '=', 'incidence_priorities.id')
            ->select('incidence_priorities.code', DB::raw('COALESCE(COUNT(*), 0) as total'))
            ->groupBy('incidence_priorities.code', 'incidence_priorities.id')
            ->orderBy('incidence_priorities.id')
            ->pluck('total', 'code')
            ->toArray();

        return array_map('intval', $result);
    }

    /**
     * Get user workload
     */
    public function getUserWorkload(int $projectId): array
    {
        $result = Incidence::where('project_id', $projectId)
            ->whereNotNull('assigned_user_id')
            ->join('users', 'incidences.assigned_user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COALESCE(COUNT(*), 0) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->toArray();

        return array_map('intval', $result);
    }

    /**
     * Get count of tasks expiring this week
     */
    public function getExpiringTasksCount(int $projectId): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Estados que NO están terminados (usando códigos)
        $activeStates = IncidenceState::whereNotIn('code', [
            IncidenceStateTransitionService::STATE_COMPLETED,
            IncidenceStateTransitionService::STATE_FINISHED
        ])->pluck('id');

        return (int) Incidence::where('project_id', $projectId)
            ->whereBetween('due_date', [$startOfWeek, $endOfWeek])
            ->whereIn('incidence_state_id', $activeStates)
            ->count();
    }

    /**
     * Get daily trends for created and completed tasks
     */
    public function getDailyTrends(int $projectId, Carbon $startDate, Carbon $endDate): array
    {
        $runningStateId = IncidenceState::where('code', IncidenceStateTransitionService::STATE_RUNNING)->value('id');

        // Tasks created per day
        $createdTasks = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(COUNT(*), 0) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date')
            ->map(fn($value) => (int) $value)
            ->toArray();

        // Tasks completed per day (usando código FIN)
        $completedTasks = Incidence::where('project_id', $projectId)
            ->whereHas('incidenceState', fn($q) => $q->where('code', IncidenceStateTransitionService::STATE_FINISHED))
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COALESCE(COUNT(*), 0) as count'))
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->pluck('count', 'date')
            ->map(fn($value) => (int) $value)
            ->toArray();

        // Tasks in progress (usando código EJE)
        $inProgressTasks = Incidence::where('project_id', $projectId)
            ->whereHas('incidenceState', fn($q) => $q->where('code', IncidenceStateTransitionService::STATE_RUNNING))
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COALESCE(COUNT(*), 0) as count'))
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->pluck('count', 'date')
            ->map(fn($value) => (int) $value)
            ->toArray();

        // Merge data
        $allDates = array_unique(array_merge(
            array_keys($createdTasks),
            array_keys($completedTasks),
            array_keys($inProgressTasks)
        ));

        $result = [];

        foreach ($allDates as $date) {
            $result[$date] = [
                'created' => (int) ($createdTasks[$date] ?? 0),
                'completed' => (int) ($completedTasks[$date] ?? 0),
                'in_progress' => (int) ($inProgressTasks[$date] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get high priority tasks (critical y high)
     */
    public function getHighPriorityTasks(int $projectId)
    {
        // IDs de tipos de incidencia
        $typeIds = IncidenceType::whereIn('code', ['TASK', 'BUG', 'SUBTASK'])->pluck('id');

        // IDs de prioridades (CRT y ALT)
        $priorityIds = IncidencePriority::whereIn('code', ['CRT', 'ALT'])->pluck('id');

        return Incidence::query()
            ->select([
                'incidences.id',
                'incidences.title',
                'incidence_priorities.code as priority_code',
                'incidence_priorities.priority as priority_name',
                'users.name as assigned_user_name',
                'incidence_states.code as status_code',
                'incidence_states.state as status_name'
            ])
            ->leftJoin('incidence_priorities', 'incidences.incidence_priority_id', '=', 'incidence_priorities.id')
            ->leftJoin('users', 'incidences.assigned_user_id', '=', 'users.id')
            ->leftJoin('incidence_states', 'incidences.incidence_state_id', '=', 'incidence_states.id')
            ->where('incidences.project_id', $projectId)
            ->whereIn('incidences.incidence_type_id', $typeIds)
            ->whereIn('incidences.incidence_priority_id', $priorityIds)
            ->orderByRaw("
                CASE 
                    WHEN incidence_priorities.code = 'CRT' THEN 1
                    WHEN incidence_priorities.code = 'ALT' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('incidences.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($incidence) {
                return [
                    'id' => (int) $incidence->id,
                    'title' => $incidence->title,
                    'priority' => [
                        'code' => $incidence->priority_code,
                        'name' => $incidence->priority_name,
                    ],
                    'assigned_user' => $incidence->assigned_user_name ?? 'Sin asignar',
                    'status' => [
                        'code' => $incidence->status_code,
                        'name' => $incidence->status_name,
                    ],
                ];
            });
    }

    /**
     * Get recent incidences
     */
    public function getRecentIncidences(int $projectId)
    {
        // Tipos de incidencia a incluir
        $typeIds = IncidenceType::whereIn('code', ['HST', 'TASK', 'BUG', 'SUB'])->pluck('id');

        return Incidence::query()
            ->select([
                'incidences.id',
                'incidences.title',
                'incidences.description',
                'incidences.created_at',
                'incidence_priorities.code as priority_code',
                'incidence_priorities.priority as priority_name',
                'incidence_states.code as state_code',
                'incidence_states.state as state_name',
                'creator.name as created_by_name'
            ])
            ->leftJoin('incidence_priorities', 'incidences.incidence_priority_id', '=', 'incidence_priorities.id')
            ->leftJoin('incidence_states', 'incidences.incidence_state_id', '=', 'incidence_states.id')
            ->leftJoin('users as creator', 'incidences.created_by_id', '=', 'creator.id')
            ->where('incidences.project_id', $projectId)
            ->whereIn('incidences.incidence_type_id', $typeIds)
            ->orderBy('incidences.created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($incidence) {
                return [
                    'id' => (int) $incidence->id,
                    'title' => $incidence->title,
                    'description' => $incidence->description,
                    'priority' => [
                        'code' => $incidence->priority_code,
                        'name' => $incidence->priority_name,
                    ],
                    'state' => [
                        'code' => $incidence->state_code,
                        'name' => $incidence->state_name,
                    ],
                    'created_at' => $incidence->created_at->format('Y-m-d H:i:s'),
                    'created_by' => $incidence->created_by_name ?? 'Sistema',
                ];
            });
    }
}