<?php

namespace App\Services\Project;

use App\Models\Team;
use App\Models\Project;
use App\Models\Incidence;
use App\Models\IncidenceState;
use App\Models\TeamUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamManagementService
{
    /**
     * Obtener todos los equipos con su información de gestión por proyecto
     */
    public function getTeamManagementData(): Collection
    {
        // Obtener todos los equipos
        $teams = Team::all();

        $result = collect();

        foreach ($teams as $team) {
            // Obtener los usuarios del equipo desde TeamUser
            $teamUsers = TeamUser::where('team_id', $team->id)
                ->with('user')
                ->get();

            $users = $teamUsers->pluck('user')->filter();

            // Agrupar miembros por proyecto
            $membersByProject = $this->groupMembersByProject($users, $team->id);

            foreach ($membersByProject as $projectId => $data) {
                if (!$data['project']) {
                    continue;
                }

                // Obtener los epics (incidencias sin parent) del equipo para este proyecto
                $epics = $this->getTeamEpics($team->id, $projectId, $data['member_ids']);

                // Si hay múltiples epics, crear un registro por cada uno
                if ($epics->count() > 0) {
                    foreach ($epics as $epic) {
                        $result->push($this->buildTeamProjectData($team, $data['project'], $data['member_ids'], $epic));
                    }
                } else {
                    // Si no hay epics, un registro sin epic
                    $result->push($this->buildTeamProjectData($team, $data['project'], $data['member_ids']));
                }
            }
        }

        return $result;
    }

    /**
     * Agrupar miembros del equipo por proyecto
     */
    private function groupMembersByProject(Collection $users, int $teamId): array
    {
        $grouped = [];

        foreach ($users as $user) {
            // Obtener los roles del usuario en proyectos (projectRoles es la relación en User)
            foreach ($user->projectRoles as $projectRole) {
                $project = $projectRole->project;
                if (!$project) {
                    continue;
                }

                $projectId = $project->id;

                if (!isset($grouped[$projectId])) {
                    $grouped[$projectId] = [
                        'project' => $project,
                        'member_ids' => []
                    ];
                }

                if (!in_array($user->id, $grouped[$projectId]['member_ids'])) {
                    $grouped[$projectId]['member_ids'][] = $user->id;
                }
            }
        }

        return $grouped;
    }

    /**
     * Obtener los epics (tareas padre) del equipo en un proyecto
     */
    private function getTeamEpics(int $teamId, int $projectId, array $memberIds): Collection
    {
        return Incidence::where('project_id', $projectId)
            ->whereNull('parent_incidence_id') // Epics son tareas sin padre
            ->whereIn('assigned_user_id', $memberIds)
            ->with(['assignedUser', 'incidenceState'])
            ->get();
    }

    /**
     * Construir los datos para un equipo-proyecto específico
     */
    private function buildTeamProjectData(Team $team, Project $project, array $memberIds, ?Incidence $epic = null): array
    {
        // Obtener todas las tareas de los miembros del equipo en este proyecto
        $tasks = $this->getTeamTasks($memberIds, $project->id, $epic?->id);

        $totalTasks = $tasks->count();
        $completedTasks = $this->getCompletedTasksCount($tasks);
        $overdueTasks = $this->getOverdueTasksCount($tasks);

        $state = $this->determineTeamState($tasks);

        return [
            'contract_number' => $project->ContractNo,
            'client' => $team->client ? [
                'id' => $team->client->id,
                'Nombre' => $team->client->Nombre,
            ] : null,
            'object_contract' => $project->objectContract,
            'team_name' => $team->name,
            'members_count' => count($memberIds),
            'created_at' => $team->created_at,
            'state' => $state,
            'progress' => $this->calculateProgress($completedTasks, $totalTasks),
            'total_tasks' => $totalTasks,
            'overdue_tasks' => $overdueTasks,
            'days_remaining' => $this->calculateDaysRemaining($project),
            'manage_tasks' => $epic ? $epic->title : 'Sin Epic Asignado',
            'epic_id' => $epic?->id,
            'project_id' => $project->id,
            'team_id' => $team->id,
        ];
    }

    /**
     * Obtener tareas de los miembros del equipo
     */
    private function getTeamTasks(array $memberIds, int $projectId, ?int $epicId = null): Collection
    {
        $query = Incidence::where('project_id', $projectId)
            ->whereIn('assigned_user_id', $memberIds)
            ->with('incidenceState');

        if ($epicId) {
            // Si hay epic, obtener tareas hijas de ese epic
            $query->where('parent_incidence_id', $epicId);
        } else {
            // Si no hay epic, obtener tareas que no sean epics (tienen padre)
            $query->whereNotNull('parent_incidence_id');
        }

        return $query->get();
    }

    /**
     * Contar tareas completadas
     */
    private function getCompletedTasksCount(Collection $tasks): int
    {
        $completedStates = ['Terminada', 'Terminada (fuera de plazo)', 'Finalizada'];

        return $tasks->filter(function ($task) use ($completedStates) {
            return $task->incidenceState &&
                in_array($task->incidenceState->state, $completedStates);
        })->count();
    }

    /**
     * Contar tareas atrasadas
     */
    private function getOverdueTasksCount(Collection $tasks): int
    {
        $today = now();
        $completedStates = ['Terminada', 'Terminada (fuera de plazo)', 'Finalizada'];

        return $tasks->filter(function ($task) use ($today, $completedStates) {
            // Está atrasada si: tiene due_date, es menor a hoy, y no está completada
            return $task->due_date &&
                $task->due_date < $today &&
                (!$task->incidenceState || !in_array($task->incidenceState->state, $completedStates));
        })->count();
    }

    /**
     * Determinar el estado del equipo
     */
    private function determineTeamState(Collection $tasks): string
    {
        $completedStates = ['Terminada', 'Terminada (fuera de plazo)', 'Finalizada'];

        $pendingTasks = $tasks->filter(function ($task) use ($completedStates) {
            return !$task->incidenceState || !in_array($task->incidenceState->state, $completedStates);
        });

        return $pendingTasks->count() > 0 ? 'Activo' : 'Completado';
    }

    /**
     * Calcular porcentaje de avance
     */
    private function calculateProgress(int $completed, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calcular días restantes del contrato
     */
    private function calculateDaysRemaining(Project $project): int
    {
        if (!$project->end_date) {
            return 0;
        }

        $today = now();
        $endDate = $project->end_date;

        if ($endDate < $today) {
            return 0;
        }

        return $today->diffInDays($endDate);
    }
}