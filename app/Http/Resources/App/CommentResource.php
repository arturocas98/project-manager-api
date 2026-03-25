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

                    if (!$user) {
                        return null;
                    }

                    $projectRole = $user->projectRoles->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'user_role' => $projectRole ? [
                            'id' => $projectRole->id,
                            'type' => $projectRole->type,
                            'code' => $projectRole->code,
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
                'attachments' => $this->getMedia('documents')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->file_name,
                        'url' => $media->getUrl(),
                        'size' => $media->size,
                        'mime_type' => $media->mime_type,
                    ];
                }),
            ],
            'links' => [
            ],
        ];
    }
}