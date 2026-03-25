<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'text' => $this->text,
            'imagen_online' => $this->imagen_online,
            'alert' => (bool) $this->alert,
            'message_id' => $this->message_id ? [
                'id' => $this->parentMessage->id ?? $this->message_id,
                'text' => $this->parentMessage->text ?? null,
                'alert' => (bool) ($this->parentMessage->alert ?? false),
                'user_name' => $this->parentMessage->user->name ?? null,
            ] : null,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role_name' => $this->user
                    ->projectRoles()
                    ->where('project_id', $this->project_id)
                    ->first()
                    ?->type
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->getMedia('documents')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                ];
            }),
        ];

    }
}
