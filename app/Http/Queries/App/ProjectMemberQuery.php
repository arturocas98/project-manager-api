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
     * Construir query base usando Eloquent
     */
    private function buildBaseQuery(): Builder
    {
        return ProjectUser::with([
            'role',
            'user',
            'role.permissionScheme.scheme.permissions'
        ])
            ->whereHas('role', function ($q) {
                $q->where('project_id', $this->project->id);
            })
            ->whereNull('deleted_at');
    }

    /**
     * Aplicar filtros
     */
    public function applyFilters(): self
    {
        // Filtrar por rol
        if ($this->request->filled('role')) {
            $this->query->whereHas('role', function ($q) {
                $q->where('type', $this->request->role);
            });
        }

        // Buscar por nombre o email
        if ($this->request->filled('search')) {
            $this->query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->request->search . '%')
                    ->orWhere('email', 'like', '%' . $this->request->search . '%');
            });
        }

        // Filtrar por fecha asignación
        if ($this->request->filled('assigned_from')) {
            $this->query->whereDate('created_at', '>=', $this->request->assigned_from);
        }

        if ($this->request->filled('assigned_to')) {
            $this->query->whereDate('created_at', '<=', $this->request->assigned_to);
        }

        return $this;
    }

    /**
     * Aplicar ordenamiento
     */
    public function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedFields = [
            'assigned_at' => 'created_at',
            'updated_at' => 'updated_at'
        ];

        if (array_key_exists($sortField, $allowedFields)) {
            $this->query->orderBy($allowedFields[$sortField], $sortDirection);
        } else {
            $this->query->orderBy('created_at', 'desc');
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
     * Obtener estadísticas por rol
     */
    public function getRoleStats(): array
    {
        return $this->project->roles()
            ->withCount('users')
            ->get()
            ->map(fn ($role) => [
                'role_id' => $role->id,
                'role_type' => $role->type,
                'members_count' => $role->users_count,
            ])
            ->toArray();
    }
}