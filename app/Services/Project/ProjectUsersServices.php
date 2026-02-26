<?php

namespace App\Services\Project;

use App\Http\Queries\App\ProjectUserQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectUsersServices
{
    public function __construct(
        private ProjectUserQuery $query
    ) {}

    /**
     * Get users not assigned to a specific project
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUnassignedUsers(int $projectId, int $authenticatedUserId)
    {
        // Puedes agregar lógica de caché aquí si es necesario
        return $this->query->getUnassignedUsers($projectId, $authenticatedUserId);
    }

    /**
     * Get users not assigned to project with search
     *
     * @param int $projectId
     * @param int $authenticatedUserId
     * @param string|null $search
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function searchUnassignedUsers(
        int $projectId,
        int $authenticatedUserId,
        ?string $search = null
    ) {
        return $this->query->searchUnassignedUsers(
            $projectId,
            $authenticatedUserId,
            $search
        );
    }
}