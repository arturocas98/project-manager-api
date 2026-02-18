<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'priority' => $this->priority,
            'project_id' => $this->project_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'type' => $this->incidenceType ? [
                'id' => $this->incidenceType->id,
                'type' => $this->incidenceType->type,
            ] : null,

            'state' => $this->incidenceState ? [
                'id' => $this->incidenceState->id,
                'state' => $this->incidenceState->state,
            ] : null,

            'created_by' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ] : null,

            'assigned_to' => null, // Siempre null al crear

            'parent' => $this->parentIncidence ? [
                'id' => $this->parentIncidence->id,
                'title' => $this->parentIncidence->title,
            ] : null,
        ];
    }
}