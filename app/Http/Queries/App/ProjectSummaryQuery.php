<?php

namespace App\Http\Queries\App;

use App\Models\Incidence;
use App\Models\IncidencePriority;
use App\Models\IncidenceState;
use App\Models\IncidenceType;
use Carbon\Carbon;
use DB;

class ProjectSummaryQuery
{
    /**
     * Get base statistics for KPIs
     */
    public function getBaseStatistics(int $projectId): array
    {
        // Get state IDs for "in progress" and "finished"
        $inProgressStateId = IncidenceState::where('state', 'progress')->value('id');
        $finishedStateId = IncidenceState::where('state', 'finished')->value('id');

        // Get priority ID for "critical"
        $criticalPriorityId = IncidencePriority::where('priority', 'critical')->value('id');

        // Define date ranges
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Current month statistics
        $currentStats = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->selectRaw('
                COALESCE(COUNT(*), 0) as total,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as finished,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as critical
            ', [$inProgressStateId, $finishedStateId, $criticalPriorityId])
            ->first();

        // Previous month statistics
        $previousStats = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->selectRaw('
                COALESCE(COUNT(*), 0) as total,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN incidence_state_id = ? THEN 1 ELSE 0 END), 0) as finished,
                COALESCE(SUM(CASE WHEN incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as critical
            ', [$inProgressStateId, $finishedStateId, $criticalPriorityId])
            ->first();

        // Calculate totals with comparisons (aseguramos que sean enteros)
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

        // Format with sign and 1 decimal place
        $formatted = number_format(abs($percentage), 1) . '%';

        return $percentage >= 0 ? "+{$formatted}" : "-{$formatted}";
    }

    /**
     * Get distribution by state (retorna array con valores numéricos asegurados)
     */
    public function getStateDistribution(int $projectId): array
    {
        $result = Incidence::where('project_id', $projectId)
            ->join('incidence_states', 'incidences.incidence_state_id', '=', 'incidence_states.id')
            ->select('incidence_states.state', DB::raw('COALESCE(COUNT(*), 0) as total'))
            ->groupBy('incidence_states.state', 'incidence_states.id')
            ->orderBy('incidence_states.id')
            ->pluck('total', 'state')
            ->toArray();

        // Aseguramos que todos los valores sean enteros
        return array_map('intval', $result);
    }

    /**
     * Get distribution by priority (retorna array con valores numéricos asegurados)
     */
    public function getPriorityDistribution(int $projectId): array
    {
        $result = Incidence::where('project_id', $projectId)
            ->join('incidence_priorities', 'incidences.incidence_priority_id', '=', 'incidence_priorities.id')
            ->select('incidence_priorities.priority', DB::raw('COALESCE(COUNT(*), 0) as total'))
            ->groupBy('incidence_priorities.priority', 'incidence_priorities.id')
            ->orderBy('incidence_priorities.id')
            ->pluck('total', 'priority')
            ->toArray();

        // Aseguramos que todos los valores sean enteros
        return array_map('intval', $result);
    }

    /**
     * Get user workload (retorna array con valores numéricos asegurados)
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

        // Aseguramos que todos los valores sean enteros
        return array_map('intval', $result);
    }

    /**
     * Get count of tasks expiring this week (siempre retorna entero)
     */
    public function getExpiringTasksCount(int $projectId): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return (int) Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->whereHas('incidenceState', function($query) {
                $query->whereNotIn('state', ['finished', 'locked']);
            })
            ->count();
    }

    /**
     * Get daily trends for created and completed tasks (valores numéricos asegurados)
     */
    public function getDailyTrends(int $projectId, Carbon $startDate, Carbon $endDate): array
    {
        $finishedStateId = IncidenceState::where('state', 'finished')->value('id');

        // Tasks created per day
        $createdTasks = Incidence::where('project_id', $projectId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(COUNT(*), 0) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date')
            ->map(function($value) {
                return (int) $value;
            })
            ->toArray();

        // Tasks completed per day (when state changed to 'finished')
        $completedTasks = Incidence::where('project_id', $projectId)
            ->where('incidence_state_id', $finishedStateId)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COALESCE(COUNT(*), 0) as count'))
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->pluck('count', 'date')
            ->map(function($value) {
                return (int) $value;
            })
            ->toArray();

        // Merge data
        $allDates = array_unique(array_merge(array_keys($createdTasks), array_keys($completedTasks)));
        $result = [];

        foreach ($allDates as $date) {
            $result[$date] = [
                'created' => (int) ($createdTasks[$date] ?? 0),
                'completed' => (int) ($completedTasks[$date] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get high priority tasks (valores numéricos asegurados en los counts)
     */
    public function getHighPriorityTasks(int $projectId)
    {
        $taskTypeId = IncidenceType::where('type', 'task')->value('id');
        $subtaskTypeId = IncidenceType::where('type', 'subtask')->value('id');

        $criticalPriorityId = IncidencePriority::where('priority', 'critical')->value('id');
        $highPriorityId = IncidencePriority::where('priority', 'high')->value('id');
        $altaPriorityId = IncidencePriority::where('priority', 'alta')->value('id');

        return Incidence::query()
            ->select([
                'incidences.id',
                'incidences.title',
                'incidence_priorities.priority',
                'users.name as assigned_user_name',
                'projects.name as project_name',
                'incidence_states.state as status' // 👈 agregado
            ])
            ->leftJoin('incidence_priorities', 'incidences.incidence_priority_id', '=', 'incidence_priorities.id')
            ->leftJoin('users', 'incidences.assigned_user_id', '=', 'users.id')
            ->leftJoin('projects', 'incidences.project_id', '=', 'projects.id')
            ->leftJoin('incidence_states', 'incidences.incidence_state_id', '=', 'incidence_states.id') // 👈 agregado
            ->where('incidences.project_id', $projectId)
            ->whereIn('incidences.incidence_type_id', array_filter([$taskTypeId, $subtaskTypeId]))
            ->whereIn('incidences.incidence_priority_id', [
                $criticalPriorityId,
                $highPriorityId,
                $altaPriorityId
            ])
            ->orderByRaw("
            CASE 
                WHEN incidence_priorities.priority IN ('critical', 'critica') THEN 1
                WHEN incidence_priorities.priority IN ('high', 'alta') THEN 2
                ELSE 3
            END
        ")
            ->orderBy('incidences.created_at', 'desc')
            ->get()
            ->map(function ($incidence) {
                return [
                    'id' => (int) $incidence->id,
                    'title' => $incidence->title,
                    'priority' => $incidence->priority,
                    'assigned_user_name' => $incidence->assigned_user_name ?? 'Unassigned',
                    'project_name' => $incidence->project_name,
                    'status' => $incidence->status,
                ];
            });
    }

    /**
     * Get recent incidences (valores numéricos asegurados)
     */
    public function getRecentIncidences(int $projectId)
    {
        // Get type IDs for specified types
        $typeIds = IncidenceType::whereIn('type', [
            'history_user',
            'task',
            'bug',
            'subtask'
        ])->pluck('id')->toArray();

        return Incidence::query()
            ->select([
                'incidences.id',
                'incidences.title',
                'incidences.description',
                'incidences.created_at',
                'incidence_priorities.priority',
                'incidence_states.state',
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
                    'priority' => $incidence->priority,
                    'state' => $incidence->state,
                    'created_at' => $incidence->created_at->format('Y-m-d H:i:s'),
                    'created_by_name' => $incidence->created_by_name ?? 'System',
                ];
            });
    }

    /**
     * Versión optimizada de getBaseStatistics con una sola consulta
     */
    public function getBaseStatisticsOptimized(int $projectId): array
    {
        // Get reference IDs
        $inProgressStateId = IncidenceState::where('state', 'progress')->value('id');
        $finishedStateId = IncidenceState::where('state', 'finished')->value('id');
        $criticalPriorityId = IncidencePriority::where('priority', 'critical')->value('id');

        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Single query with conditional aggregation
        $stats = Incidence::where('project_id', $projectId)
            ->where(function($query) use ($currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd) {
                $query->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                    ->orWhereBetween('created_at', [$previousMonthStart, $previousMonthEnd]);
            })
            ->selectRaw('
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as current_total,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as previous_total,
                
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_state_id = ? THEN 1 ELSE 0 END), 0) as current_in_progress,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_state_id = ? THEN 1 ELSE 0 END), 0) as previous_in_progress,
                
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_state_id = ? THEN 1 ELSE 0 END), 0) as current_finished,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_state_id = ? THEN 1 ELSE 0 END), 0) as previous_finished,
                
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as current_critical,
                COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? AND incidence_priority_id = ? THEN 1 ELSE 0 END), 0) as previous_critical
            ', [
                $currentMonthStart, $currentMonthEnd,
                $previousMonthStart, $previousMonthEnd,

                $currentMonthStart, $currentMonthEnd, $inProgressStateId,
                $previousMonthStart, $previousMonthEnd, $inProgressStateId,

                $currentMonthStart, $currentMonthEnd, $finishedStateId,
                $previousMonthStart, $previousMonthEnd, $finishedStateId,

                $currentMonthStart, $currentMonthEnd, $criticalPriorityId,
                $previousMonthStart, $previousMonthEnd, $criticalPriorityId
            ])
            ->first();

        return [
            'total' => [
                'value' => (int) ($stats->current_total ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($stats->current_total ?? 0),
                    (int) ($stats->previous_total ?? 0)
                ),
            ],
            'in_progress' => [
                'value' => (int) ($stats->current_in_progress ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($stats->current_in_progress ?? 0),
                    (int) ($stats->previous_in_progress ?? 0)
                ),
            ],
            'finished' => [
                'value' => (int) ($stats->current_finished ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($stats->current_finished ?? 0),
                    (int) ($stats->previous_finished ?? 0)
                ),
            ],
            'critical' => [
                'value' => (int) ($stats->current_critical ?? 0),
                'comparison' => $this->calculateComparison(
                    (int) ($stats->current_critical ?? 0),
                    (int) ($stats->previous_critical ?? 0)
                ),
            ],
        ];
    }
}