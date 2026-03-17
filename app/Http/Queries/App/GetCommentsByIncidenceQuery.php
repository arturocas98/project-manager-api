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
            'createdBy', // Primero carga el usuario
            'createdBy.projectRoles' => function ($query) use ($projectId) {
                // Filtra los roles por el proyecto específico
                $query->where('project_id', $projectId)
                    ->select('project_roles.*'); // Selecciona los campos que necesitas
            }
        ])
            ->where('incidence_id', $incidenceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}