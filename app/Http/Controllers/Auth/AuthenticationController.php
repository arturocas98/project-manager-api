<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\Generate2FACache;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Auth')]
class AuthenticationController
{
    /**
     * Login
     *
     * Generates a personal access token for a user.
     */
    #[Unauthenticated]
    #[Response(content: ['access_token' => '...'], description: 'With 2FA disabled')]
    #[Response(content: ['two_factor_authentication' => true], description: 'With 2FA enabled')]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
    public function login(LoginRequest $request, Generate2FACache $generate2FACache): JsonResource
    {
        $user = $request->getAuthenticatedUser();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $generate2FACache($user);

            return new JsonResource([
                'two_factor_authentication' => true,
            ]);
        }

        return new JsonResource([
            'access_token' => $user->createToken("users:{$user->getKey()}")->accessToken,
        ]);
    }

    /**
     * Logout
     *
     * Revoke the token of the authenticated user.
     */
    #[Authenticated]
    #[Response(status: JsonResponse::HTTP_NO_CONTENT)]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()?->revoke();

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
