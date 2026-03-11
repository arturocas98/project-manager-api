<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $projectId = $this->when(isset($this->resource->project_id), $this->resource->project_id, null);
        return [
            'data' =>[
                'id' => $this->id,
                'description' => $this->description,
                'incidence' => $this->whenLoaded('incidence', function () {
                    return [
                        'id' => $this->incidence->id,
                        'name' => $this->incidence->title,
                    ];
                }),
                'createdBy' => $this->whenLoaded('createdBy', function () use ($projectId) {
                    $user = $this->createdBy;

                    $userRole = $user->projectRoles->firstWhere('project_id', $projectId);

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'user_role' => $userRole ? [
                            'id' => $userRole->id,
                            'type' => $userRole->type,
                        ] : null,
                    ];
                }),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'meta' => [
                'timestamps' => [
                    'created' => $this->created_at?->toIso8601String(),
                    'updated' => $this->updated_at?->toIso8601String(),
                ],
            ],
            'links' => [
            ],
        ];
    }
}