<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Generate2FACache
{
    public function __invoke(User $user): void
    {
        Cache::add(
            key: 'login.'.$user->getEmailForVerification(),
            value: encrypt($user->getKey()),
            ttl: now()->addMinutes(config('auth.2fa_api_cache_timeout', 3))
        );
    }
}
