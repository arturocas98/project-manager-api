<?php

namespace App\Services\Incidence;

use App\Actions\App\Incidence\CreateIncidenceAction;
use App\Exceptions\ProjectException;
use App\Http\Queries\App\IncidenceQuery;
use App\Models\Incidence;
use App\Models\Project;
use Illuminate\Support\Collection;

class CreateIndiceService
{
    public function __construct(
        private IncidenceQuery $incidenceQuery,
        private CreateIncidenceAction $createIncidenceAction
    ) {}

    public function getProjectIncidences(int $projectId): Collection
    {
        return $this->incidenceQuery
            ->byProject($projectId)
            ->withDefaultRelations()
            ->orderByLatest()
            ->get();
    }

    public function createIncidence(int $projectId, array $data, int $createdById): Incidence
    {
        return $this->createIncidenceAction->execute($projectId, $data, $createdById);
    }

    public function validateProjectAccess(Project $project): void
    {
        $userId = auth()->id();

        $hasAccess = $project->roles()
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $hasAccess) {
            throw new ProjectException(
                'Acceso denegado: No tienes acceso a este proyecto',
                403
            );
        }
    }

    public function loadIncidenceRelations(Incidence $incidence): Incidence
    {
        return $incidence->load([
            'incidenceType',
            'incidenceState',
            'createdBy:id,name,email',
            'assignedUser:id,name,email',
            'parentIncidence:id,title',
        ]);
    }
}
