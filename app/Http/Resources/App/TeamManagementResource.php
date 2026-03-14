<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamManagementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contract_number' => $this->resource['contract_number'],
            'client' => $this->resource['client'],
            'object_contract' => $this->resource['object_contract'],
            'team_name' => $this->resource['team_name'],
            'members_count' => $this->resource['members_count'],
            'created_at' => $this->resource['created_at']->format('d/m/Y'),
            'state' => $this->resource['state'],
            'progress' => $this->resource['progress'] . '%',
            'total_tasks' => $this->resource['total_tasks'],
            'overdue_tasks' => $this->resource['overdue_tasks'],
            'days_remaining' => $this->resource['days_remaining'],
            'manage_tasks' => $this->resource['manage_tasks'],
            // Metadatos adicionales (útiles para debugging, podrías quitarlos en producción)
            'meta' => [
                'epic_id' => $this->resource['epic_id'],
                'project_id' => $this->resource['project_id'],
                'team_id' => $this->resource['team_id'],
            ],
            'links' => []
        ];
    }
}