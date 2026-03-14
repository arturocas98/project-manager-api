<?php

namespace App\Http\Controllers;

use App\Actions\App\AddTeamMemberAction;
use App\Actions\App\CreateTeamAction;
use App\Actions\App\RemoveTeamMemberAction;
use App\Http\Queries\App\TeamQuery;
use App\Http\Requests\App\TeamMemberRequest;
use App\Http\Requests\App\TeamRequest;
use App\Http\Resources\App\TeamCollection;
use App\Http\Resources\App\TeamManagementResource;
use App\Http\Resources\App\TeamResource;
use App\Models\Team;
use App\Services\Project\TeamManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Illuminate\Http\Request;

#[Group('App')]
#[Subgroup('Team')]
#[Authenticated]
class TeamController extends Controller
{
    public function __construct(
        protected CreateTeamAction $createTeamAction,
        protected AddTeamMemberAction $addTeamMemberAction,
        protected RemoveTeamMemberAction $removeTeamMemberAction,
        protected TeamQuery $teamQuery,
        protected TeamManagementService $teamManagementService
    ) {}

    /**
     * Store - Solo crea el equipo
     */
    public function index(Request $request): TeamCollection
    {
        $teams = $this->teamQuery
            ->withAllRelations()
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->has('user_id'), function ($query) use ($request) {
                // Filtrar equipos donde un usuario específico es miembro
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('users.id', $request->user_id);
                });
            })
            ->when($request->has('created_by'), function ($query) use ($request) {
                $query->where('created_by_id', $request->created_by);
            })
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate($request->get('per_page', 15));

        return new TeamCollection($teams);
    }



    /**
     * Display the specified team.
     */
    public function show(int $id): TeamResource
    {
        $team = $this->teamQuery->findWithRelations($id);

        if (!$team) {
            abort(404, 'Equipo no encontrado');
        }

        // Verificar permisos (opcional)
        // if (!$this->authorize('view', $team)) {
        //     abort(403, 'No tienes permiso para ver este equipo');
        // }

        return new TeamResource($team);
    }
    public function store(TeamRequest $request): TeamResource
    {
        $team = $this->createTeamAction->execute(
            $request->validated(),
            Auth::id()
        );

        $teamWithRelations = $this->teamQuery->findWithRelations($team->id);

        return new TeamResource($teamWithRelations);
    }

    public function update(TeamRequest $request, Team $team): TeamResource
    {
        // Verificar permisos (opcional)
        // $this->authorize('update', $team);

        $team->update([
            'name' => $request->name,
            'type' => $request->type ?? $team->type,
        ]);

        // Refrescar con relaciones
        $teamWithRelations = $this->teamQuery->findWithRelations($team->id);

        return new TeamResource($teamWithRelations);
    }

    /**
     * Add member al equipo
     */
    public function addMember(Team $team, TeamMemberRequest $request): JsonResponse
    {
        try {
            $this->addTeamMemberAction->execute(
                $team->id,
                $request->user_id
            );

            $teamWithRelations = $this->teamQuery->findWithRelations($team->id);

            return response()->json([
                'message' => 'Miembro agregado correctamente',
                'team' => new TeamResource($teamWithRelations),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al agregar miembro',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove member del equipo
     */
    public function removeMember(Team $team, TeamMemberRequest $request): JsonResponse
    {
        if ($team->created_by_id === $request->user_id) {
            return response()->json([
                'message' => 'No se puede eliminar al creador del equipo',
            ], 400);
        }

        $deleted = $this->removeTeamMemberAction->execute(
            $team->id,
            $request->user_id
        );

        if ($deleted) {
            $teamWithRelations = $this->teamQuery->findWithRelations($team->id);

            return response()->json([
                'message' => 'Miembro eliminado correctamente',
                'team' => new TeamResource($teamWithRelations),
            ]);
        }

        return response()->json([
            'message' => 'El miembro no existe en este equipo',
        ], 404);
    }
    public function destroy(Team $team): JsonResponse
    {
        // Verificar permisos (opcional)
        // $this->authorize('delete', $team);

        // Eliminar relaciones en team_user primero (por foreign key constraints)
        $team->users()->detach();

        // Eliminar el equipo
        $team->delete();

        return response()->json([
            'message' => 'Equipo eliminado correctamente'
        ]);
    }
    public function management(): JsonResponse
    {
        $teamData = $this->teamManagementService->getTeamManagementData();

        return response()->json([
            'data' => TeamManagementResource::collection($teamData),
            'meta' => [
                'total_teams_projects' => $teamData->count(),
                'executive_summary' => $this->getExecutiveSummary($teamData)
            ]
        ]);
    }

    private function getExecutiveSummary(Collection $data): array
    {
        $activeTeams = $data->where('state', 'Activo');
        $totalCollaborators = $data->sum('members_count');
        $totalTasks = $data->sum('total_tasks');
        $tasksExecuting = 0; // Esto requeriría lógica adicional
        $overdueTasks = $data->sum('overdue_tasks');
        $avgProgress = $data->avg('progress');
        $completedTasks = $data->sum(function ($item) {
            // Esto requeriría lógica adicional o modificar el service
            return 0;
        });

        // Contratos con menos de 30 días
        $contractsUnder30 = $data->filter(function ($item) {
            return $item['days_remaining'] < 30;
        })->count();

        $totalContracts = $data->groupBy('contract_number')->count();
        $percentageUnder30 = $totalContracts > 0
            ? round(($contractsUnder30 / $totalContracts) * 100)
            : 0;

        return [
            'active_teams' => $activeTeams->count(),
            'total_collaborators' => $totalCollaborators,
            'total_tasks' => $totalTasks,
            'tasks_executing' => $tasksExecuting,
            'overdue_tasks' => $overdueTasks,
            'avg_progress' => round($avgProgress, 2) . '%',
            'completed_tasks' => $completedTasks,
            'contracts_under_30_days' => $percentageUnder30 . '%',
        ];
    }
}