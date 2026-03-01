<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'route' => $this->route,
            'icon' => $this->icon,
            'created_at' => $this->created_at?->format(config('resources.date_time_format')),
        ];
    }
}
