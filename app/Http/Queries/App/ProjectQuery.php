<?php

namespace App\Http\Queries\App;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class ProjectQuery
{
    private Builder $query;

    private Request $request;

    private int $userId;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->userId = auth()->id(); // <-- ID del usuario autenticado
        $this->query = Project::query();
        $this->applyUserFilter(); // <-- Filtro aplicado SIEMPRE
    }

    /**
     * FILTRO PRINCIPAL: SOLO proyectos donde el usuario tiene rol
     */
    private function applyUserFilter(): void
    {
        $this->query->whereHas('roles.users', function ($q) {
            $q->where('user_id', $this->userId);
        });
    }

    /**
     * Filtros opcionales desde el request
     */
    public function applyFilters(): self
    {
        // Búsqueda por nombre, key o descripción
        if ($this->request->has('search')) {
            $this->query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->request->search.'%')
                    ->orWhere('key', 'like', '%'.$this->request->search.'%')
                    ->orWhere('description', 'like', '%'.$this->request->search.'%');
            });
        }

        // Filtrar por rol específico
        if ($this->request->has('role')) {
            $this->query->whereHas('roles', function ($q) {
                $q->where('type', $this->request->role)
                    ->whereHas('users', fn ($q) => $q->where('user_id', $this->userId));
            });
        }

        // Filtrar por fecha de creación
        if ($this->request->has('created_from')) {
            $this->query->whereDate('created_at', '>=', $this->request->created_from);
        }

        if ($this->request->has('created_to')) {
            $this->query->whereDate('created_at', '<=', $this->request->created_to);
        }

        return $this;
    }

    /**
     * Ordenamiento
     */
    public function applySorting(): self
    {
        $sortField = $this->request->get('sort_by', 'created_at');
        $sortDirection = $this->request->get('sort_direction', 'desc');

        $allowedFields = ['name', 'key', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedFields)) {
            $this->query->orderBy($sortField, $sortDirection);
        } else {
            $this->query->latest(); // Por defecto: más recientes primero
        }

        return $this;
    }

    /**
     * Cargar relaciones necesarias (solo del usuario autenticado)
     */
    public function applyEagerLoading(): self
    {
        $this->query->with([
            'roles' => function ($q) {
                // SOLO el rol del usuario autenticado
                $q->whereHas('users', fn ($q) => $q->where('user_id', $this->userId))
                    ->with(['permissionScheme.scheme.permissions']);
            },
            'createdBy',
        ]);

        return $this;
    }

    /**
     * Obtener resultados PAGINADOS
     */
    public function paginate()
    {
        $this->applyFilters()
            ->applySorting()
            ->applyEagerLoading();

        $perPage = $this->request->get('per_page', 15);

        return $this->query->paginate($perPage);
    }

    /**
     * Obtener resultados SIN paginación
     */
    public function get()
    {
        $this->applyFilters()
            ->applySorting()
            ->applyEagerLoading();

        return $this->query->get();
    }

    /**
     * Buscar un proyecto específico (con verificación de acceso)
     */
    public function find(int $id)
    {
        return $this->query
            ->where('id', $id)
            ->with([
                'roles' => function ($q) {
                    $q->whereHas('users', fn ($q) => $q->where('user_id', $this->userId))
                        ->with(['permissionScheme.scheme.permissions']);
                },
                'createdBy',
            ])
            ->first();
    }

    /*
     Metodos para show
    */
    public function findForShow(int $id): ?Project
    {
        // Aplicar filtro de usuario
        $this->applyUserFilter();

        // Aplicar eager loading específico para show
        $this->applyShowEagerLoading();

        // Buscar el proyecto
        return $this->query->where('id', $id)->first();
    }

    private function applyShowEagerLoading(): void
    {
        $this->query->with([
            // Rol del usuario autenticado
            'roles' => function ($q) {
                $q->whereHas('users', fn ($q) => $q->where('user_id', $this->userId))
                    ->with(['permissionScheme.scheme.permissions']);
            },
            // Creador del proyecto
            'createdBy',
            // Todos los roles del proyecto (para stats)
            'roles.users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.email');
            },
        ]);
    }
}
