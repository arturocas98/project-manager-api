<?php


namespace App\Http\Queries\App;

use App\Models\User;
use App\Models\ProjectUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectUserQuery
{
    /**
     * Get users not assigned to a specific project
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUnassignedUsers(
        int $projectId,
        int $authenticatedUserId,
        int $perPage = 15
    ): LengthAwarePaginator {
        // Get IDs of users already assigned to the project usando la relación del modelo User
        $assignedUserIds = User::whereHas('projectRoles', function($query) use ($projectId) {
            $query->whereHas('project', function($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        })
            ->pluck('id')
            ->toArray();

        return User::query()
            ->whereNotIn('id', $assignedUserIds) // Excluir usuarios asignados
            ->where('id', '!=', $authenticatedUserId) // Excluir usuario autenticado
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Search users not assigned to project
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchUnassignedUsers(
        int $projectId,
        int $authenticatedUserId,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        // Get IDs of users already assigned to the project
        $assignedUserIds = User::whereHas('projectRoles', function($query) use ($projectId) {
            $query->whereHas('project', function($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        })
            ->pluck('id')
            ->toArray();

        $query = User::query()
            ->whereNotIn('id', $assignedUserIds)
            ->where('id', '!=', $authenticatedUserId);

        // Apply search if provided
        if ($search) {
            $query->where(function(Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                // Nota: User no tiene campo 'username' en el fillable actual
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get users not assigned to project with additional filters
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @param array $options
     * @return LengthAwarePaginator
     */
    public function getFilteredUnassignedUsers(
        int $projectId,
        int $authenticatedUserId,
        array $options = []
    ): LengthAwarePaginator {
        $perPage = $options['per_page'] ?? 15;
        $search = $options['search'] ?? null;
        $excludeEmailVerified = $options['exclude_email_verified'] ?? false;
        $role = $options['role'] ?? null;

        // Get assigned user IDs
        $assignedUserIds = User::whereHas('projectRoles', function($query) use ($projectId) {
            $query->whereHas('project', function($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        })
            ->pluck('id')
            ->toArray();

        $query = User::query()
            ->whereNotIn('id', $assignedUserIds)
            ->where('id', '!=', $authenticatedUserId);

        // Apply search
        if ($search) {
            $query->where(function(Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by email verification
        if ($excludeEmailVerified) {
            $query->whereNotNull('email_verified_at');
        }

        // Filter by specific role in other projects (opcional)
        if ($role) {
            $query->whereHas('projectRoles', function($q) use ($role) {
                $q->where('type', $role);
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get count of unassigned users
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @return int
     */
    public function countUnassignedUsers(int $projectId, int $authenticatedUserId): int
    {
        $assignedUserIds = User::whereHas('projectRoles', function($query) use ($projectId) {
            $query->whereHas('project', function($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        })
            ->pluck('id')
            ->toArray();

        return User::whereNotIn('id', $assignedUserIds)
            ->where('id', '!=', $authenticatedUserId)
            ->count();
    }

    /**
     * Get users not assigned to project with their roles from other projects
     * (útil para ver en qué otros proyectos participan)
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @return LengthAwarePaginator
     */
    public function getUnassignedUsersWithOtherProjects(
        int $projectId,
        int $authenticatedUserId
    ): LengthAwarePaginator {
        $assignedUserIds = User::whereHas('projectRoles', function($query) use ($projectId) {
            $query->whereHas('project', function($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        })
            ->pluck('id')
            ->toArray();

        return User::with(['projectRoles' => function($query) {
            $query->with('project')
                ->whereHas('project'); // Solo roles que tengan proyecto
        }])
            ->whereNotIn('id', $assignedUserIds)
            ->where('id', '!=', $authenticatedUserId)
            ->orderBy('name')
            ->paginate();
    }
}