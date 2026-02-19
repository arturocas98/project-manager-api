<?php

namespace App\Services\Project;

use App\Exceptions\ProjectException;
use App\Http\Queries\App\ProjectMemberQuery;
use App\Models\Project;
use Illuminate\Http\Request;

class IndexProjectMemberService
{
    /*
     Metodos para el index
    */
    public function getMembers(Project $project, Request $request): array
    {
        // Validar acceso al proyecto
        $this->validateProjectAccess($project);

        // Crear query con filtros
        $query = new ProjectMemberQuery($project, $request);

        // Obtener resultados paginados
        $members = $query->paginate();

        // Obtener estadísticas por rol
        $roleStats = $query->getRoleStats();

        return [
            'members' => $members,
            'stats' => [
                'total' => $members->total(),
                'by_role' => $roleStats,
            ],
            'filters' => [
                'applied' => $request->all(),
                'available_roles' => $this->getAvailableRoles($project),
            ],
        ];
    }

    /**
     * Validar que el usuario tiene acceso al proyecto
     */
    private function validateProjectAccess(Project $project): void
    {
        $userId = auth()->id();

        $hasAccess = $project->roles()
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $hasAccess) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Acceso denegado',
                    'reason' => 'No tienes acceso a este proyecto',
                    'project_id' => $project->id,
                ]),
                403
            );
        }
    }

    /**
     * Obtener roles disponibles en el proyecto
     */
    private function getAvailableRoles(Project $project): array
    {
        return $project->roles()
            ->withCount('users')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'type' => $role->type,
                'members_count' => $role->users_count,
            ])
            ->toArray();
    }
}
