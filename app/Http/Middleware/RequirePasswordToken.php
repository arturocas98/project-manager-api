<?php

namespace App\Http\Middleware;

use App\Enums\HeaderName;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequirePasswordToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (JsonResponse)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->header(HeaderName::ConfirmedPasswordToken->value);

        if (! is_string($token)) {
            throw new HttpException(JsonResponse::HTTP_LOCKED, __('passwords.invalid_confirmation_token'));
        }

        try {
            $payload = explode('|', Crypt::decryptString($token));
        } catch (DecryptException $e) {
            $payload = [];
        }

        if (
            count($payload) < 2 ||
            $payload[0] != $request->user()->getKey() ||
            (time() - $payload[1]) >= config('auth.password_timeout', 3600)
        ) {
            throw new HttpException(JsonResponse::HTTP_LOCKED, __('passwords.invalid_confirmation_token'));
        }

        return $next($request);
    }
}
