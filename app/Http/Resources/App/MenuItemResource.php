<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sequence' => $this->sequence,
            'link' => new LinkResource($this->link),
            'links' => MenuItemResource::collection($this->links->sortBy('sequence')),
            'created_at' => $this->created_at?->format(config('resources.date_time_format')),
        ];
    }
}
