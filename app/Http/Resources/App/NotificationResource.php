<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'read' => $this->read,
            'link' => $this->link,
            'link_web' => $this->link_web,
            'created_at' => $this->created_at?->format(config('resources.date_time_format')),
        ];
    }
}

