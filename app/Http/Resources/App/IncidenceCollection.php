<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IncidenceCollection extends ResourceCollection
{
    private int $projectId;
    private string $projectName;

    public function __construct($resource, int $projectId, string $projectName)
    {
        parent::__construct($resource);
        $this->projectId = $projectId;
        $this->projectName = $projectName;
    }

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($incidence) {
                return [
                    'id' => $incidence->id,
                    'title' => $incidence->title,
                    'description' => $incidence->description,
                    'date' => $incidence->date,
                    'priority' => $incidence->priority,
                    'created_at' => $incidence->created_at,
                    'updated_at' => $incidence->updated_at,

                    'type' => $incidence->incidenceType ? [
                        'id' => $incidence->incidenceType->id,
                        'type' => $incidence->incidenceType->type,
                    ] : null,

                    'state' => $incidence->incidenceState ? [
                        'id' => $incidence->incidenceState->id,
                        'state' => $incidence->incidenceState->state,
                    ] : null,

                    'created_by' => $incidence->createdBy ? [
                        'id' => $incidence->createdBy->id,
                        'name' => $incidence->createdBy->name,
                        'email' => $incidence->createdBy->email,
                    ] : null,

                    'assigned_to' => $incidence->assignedUser ? [
                        'id' => $incidence->assignedUser->id,
                        'name' => $incidence->assignedUser->name,
                        'email' => $incidence->assignedUser->email,
                    ] : null,

                    'parent' => $incidence->parentIncidence ? [
                        'id' => $incidence->parentIncidence->id,
                        'title' => $incidence->parentIncidence->title,
                    ] : null,
                ];
            }),
            'meta' => [
                'total' => $this->collection->count(),
                'project' => [
                    'id' => $this->projectId,
                    'name' => $this->projectName,
                ],
            ],
            'links' => [
                'self' => route('projects.incidences.index', ['project' => $this->projectId]),
                'parent' => route('projects.show', ['project' => $this->projectId]),
                'create' => route('projects.incidences.store', ['project' => $this->projectId]),
            ],
        ];
    }
}