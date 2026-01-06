<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Events\RecoveryCodeReplaced;

#[Group('Auth')]
#[Unauthenticated]
#[Response(content: ['access_token' => '...'])]
#[ResponseFromFile(file: 'responses/429.json', status: JsonResponse::HTTP_TOO_MANY_REQUESTS)]
#[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
class TwoFactorAuthenticatedTokenController extends Controller
{
    /**
     * 2FA Login
     *
     * Attempt to authenticate a new user using the two-factor authentication code.
     */
    public function store(TwoFactorLoginRequest $request): JsonResource|JsonResponse
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        return new JsonResource([
            'access_token' => $user->createToken("users:{$user->getKey()}")->accessToken,
        ]);
    }
}
