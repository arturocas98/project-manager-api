<?php


namespace App\Http\Queries\App;

use App\Models\Project;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectMemberQuery
{
    private Project $project;
    private Request $request;
    private Builder $query;

    public function __construct(Project $project, Request $request)
    {
        $this->project = $project;
        $this->request = $request;
        $this->query = $this->buildBaseQuery();
    }

    /**
     * Construir query base
     */
    private function buildBaseQuery(): Builder
    {
        return DB::table('project_users')
            ->join('project_roles', 'project_roles.id', '=', 'project_users.project_role_id')
            ->join('users', 'users.id', '=', 'project_users.user_id')
            ->where('project_roles.project_id', $this->project->id)
            ->whereNull('project_users.deleted_at')
            ->select([
                'project_users.id as assignment_id',
                'project_users.created_at as assigned_at',
                'project_users.updated_at as updated_at',
                'project_roles.id as role_id',
                'project_roles.type as role_type',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.created_at as user_joined_at'
            ]);
    }

    /**
     * Aplicar filtros
     */
    public function applyFilters(): self
    {
        // Filtrar por rol
        if ($this->request->has('role')) {
            $this->query->where('project_roles.type', $this->request->role);
        }

        // Filtrar por nombre de usuario
        if ($this->request->has('search')) {
            $this->query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->request->search . '%')
                    ->orWhere('users.email', 'like', '%' . $this->request->search . '%');
            });
        }

        // Filtrar por fecha de asignación
        if ($this->request->has('assigned_from')) {
            $this->query->whereDate('project_users.created_at', '>=', $this->request->assigned_from);
        }

        if ($this->request->has('assigned_to')) {
            $this->query->whereDate('project_users.created_at', '<=', $this->request->assigned_to);
        }

        return $this;
    }

    /**
     * Aplicar ordenamiento
     */
    public function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'assigned_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedFields = [
            'assigned_at' => 'project_users.created_at',
            'user_name' => 'users.name',
            'role_type' => 'project_roles.type',
            'updated_at' => 'project_users.updated_at'
        ];

        if (array_key_exists($sortField, $allowedFields)) {
            $this->query->orderBy($allowedFields[$sortField], $sortDirection);
        } else {
            $this->query->orderBy('project_users.created_at', 'desc');
        }

        return $this;
    }

    /**
     * Obtener resultados paginados
     */
    public function paginate()
    {
        $this->applyFilters()->applySorting();

        $perPage = $this->request->get('per_page', 15);

        return $this->query->paginate($perPage);
    }

    /**
     * Obtener todos los resultados
     */
    public function get()
    {
        $this->applyFilters()->applySorting();

        return $this->query->get();
    }

    /**
     * Obtener estadísticas por rol
     */
    public function getRoleStats(): array
    {
        return \DB::table('project_roles')
            ->leftJoin('project_users', 'project_roles.id', '=', 'project_users.project_role_id')
            ->where('project_roles.project_id', $this->project->id)
            ->whereNull('project_users.deleted_at')
            ->groupBy('project_roles.id', 'project_roles.type')
            ->select([
                'project_roles.id as role_id',
                'project_roles.type as role_type',
                \DB::raw('COUNT(project_users.id) as members_count')
            ])
            ->get()
            ->toArray();
    }
}