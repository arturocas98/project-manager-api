<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'locked_to_modify' => $this->locked_to_modify,
            'parent_id' => $this->parent_id,
            'parent' => new RoleResource($this->whenLoaded('parent')),
            'created_at' => $this->created_at->format(config('resources.date_time_format')),
        ];
    }
}
