<?php

namespace App\Http\Queries\App;

use App\Models\TaskComent;
use App\Models\Incidence;
use Illuminate\Database\Eloquent\Collection;

class GetCommentsByIncidenceQuery
{
    public function execute(int $incidenceId, int $projectId): Collection
    {
        $incidence = Incidence::where('id', $incidenceId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        return TaskComent::with([
            'incidence:id,title',
            'createdBy.projectRoles:id,type,project_id'
        ])
            ->where('incidence_id', $incidenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}