<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'not_read' => $this->notifications->where('read', false)->count(),
            'notifications' => NotificationResource::collection($this->notifications),
        ];
    }
}

