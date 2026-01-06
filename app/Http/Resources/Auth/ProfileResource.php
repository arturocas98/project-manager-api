<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toDateTimeLocalString(),
            'two_factor_confirmed_at' => $this->two_factor_confirmed_at?->toDateTimeLocalString(),
            'profile_photo_url' => $this->profile_photo_url,
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions
                ->where('guard_name', 'api')
                ->pluck('name')
                ->toArray()
            ),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles
                ->where('guard_name', 'api')
                ->pluck('name')
                ->toArray()
            ),
            'access_permissions' => $this->getAccessPermissions(),
        ];
    }

    public function getAccessPermissions(): mixed
    {
        return $this->when($this->relationLoaded('permissions') && $this->relationLoaded('roles'),
            fn () => $this->getAllPermissions()
                ->where('guard_name', 'api')
                ->pluck('name')
                ->unique()
                ->sort()
                ->values()
                ->toArray()
        );
    }
}
