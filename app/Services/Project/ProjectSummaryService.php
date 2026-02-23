<?php

namespace App\Services\Project;
use App\Http\Queries\App\ProjectSummaryQuery;
use Carbon\Carbon;

class ProjectSummaryService
{
    public function __construct(
        private ProjectSummaryQuery $query
    ) {}

    /**
     * Get comprehensive project summary
     */
    public function getSummary(int $projectId): array
    {
        // Get base statistics
        $baseStats = $this->query->getBaseStatistics($projectId);

        // Get distribution by state
        $stateDistribution = $this->query->getStateDistribution($projectId);

        // Get distribution by priority
        $priorityDistribution = $this->query->getPriorityDistribution($projectId);

        // Get user workload
        $userWorkload = $this->query->getUserWorkload($projectId);

        // Get weekly trend
        $weeklyTrend = $this->getWeeklyTrend($projectId);

        // Get expiring tasks (due this week)
        $expiringTasks = $this->query->getExpiringTasksCount($projectId);

        $highPriorityTasks = $this->query->getHighPriorityTasks($projectId);

        $recentIncidences = $this->query->getRecentIncidences($projectId);

        return [
            'kpis' => [
                'total_tasks' => $baseStats['total'] ?? 0,
                'in_progress_tasks' => $baseStats['in_progress'] ?? 0,
                'finished_tasks' => $baseStats['finished'] ?? 0,
                'expiring_this_week' => $expiringTasks,
                'critical_priority_tasks' => $baseStats['critical'] ?? 0,
            ],
            'distribution_by_state' => $stateDistribution,
            'distribution_by_priority' => $priorityDistribution,
            'user_workload' => $userWorkload,
            'high_priority_tasks' => $highPriorityTasks,
            'recent_incidences' => $recentIncidences,
            'trends' => [
                'weekly' => $weeklyTrend,
            ],
        ];
    }

    /**
     * Get weekly trend data
     */
    private function getWeeklyTrend(int $projectId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $dailyStats = $this->query->getDailyTrends(
            $projectId,
            $startOfWeek,
            $endOfWeek
        );

        // Format for each day of the week
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $trendData = [];

        foreach ($days as $index => $dayName) {
            $date = $startOfWeek->copy()->addDays($index)->format('Y-m-d');
            $stats = $dailyStats[$date] ?? ['created' => 0, 'completed' => 0];

            $trendData[$dayName] = [
                'tasks_created' => $stats['created'],
                'tasks_completed' => $stats['completed'],
            ];
        }

        return $trendData;
    }
}