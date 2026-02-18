<?php


namespace App\Services\Incidence;

use App\Exceptions\ProjectException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Project;
use Illuminate\Support\Collection;

class IncidenceService
{
    public function getProjectIncidences(int $projectId): Collection
    {
        return (new IncidenceQuery())
            ->byProject($projectId)
            ->withDefaultRelations()
            ->orderByLatest()
            ->get();
    }

    public function validateProjectAccess(Project $project): void
    {
        $userId = auth()->id();

        $hasAccess = $project->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (!$hasAccess) {
            throw new ProjectException(
                'Acceso denegado: No tienes acceso a este proyecto',
                403
            );
        }
    }
}