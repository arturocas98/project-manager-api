<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProfileCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toDateTimeLocalString(),
                    'two_factor_confirmed_at' => $user->two_factor_confirmed_at?->toDateTimeLocalString(),
                    'profile_photo_url' => $user->profile_photo_url,
                ];
            })->values()
        ];
    }
}
