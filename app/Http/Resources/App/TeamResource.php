<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'id' => $this->id,
                'name' => $this->name,
                'type' => $this->type_code,
                ' ' => $this->whenLoaded('users', fn() => $this->users->count()),
                'members' => $this->whenLoaded('users', function () {
                    return $this->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            // El rol se manejaría aparte con Spatie
                        ];
                    });
                }),
                'created_by' => $this->whenLoaded('createdBy', fn() => [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ]),
                'created_at' => $this->created_at?->toDateTimeLocalString(),
            ],
            'meta' => [
                'created_at' => $this->created_at?->toDateTimeLocalString(),
                'updated_at' => $this->updated_at?->toDateTimeLocalString(),
            ],
            'links' => [
            ],
        ];
    }
}