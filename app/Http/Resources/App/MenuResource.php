<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role_id' => $this->role_id,
            'links' => MenuItemResource::collection($this->items->whereNull('parent_menu_item_id')->sortBy('sequence')),
            'created_at' => $this->created_at?->format(config('resources.date_time_format')),
        ];
    }
}
