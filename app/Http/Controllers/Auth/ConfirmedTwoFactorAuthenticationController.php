<?php

namespace App\Http\Controllers\Auth;

use App\Enums;
use App\Http\Requests\Auth\ConfirmedTwoFactorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;

#[Group('Auth')]
#[Subgroup('2FA Settings')]
#[Authenticated]
#[Header(Enums\HeaderName::ConfirmedPasswordToken->value)]
#[Response(status: JsonResponse::HTTP_NO_CONTENT)]
#[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
#[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
class ConfirmedTwoFactorAuthenticationController extends Controller
{
    /**
     * Confirm 2FA
     *
     * Confirm two-factor authentication for the user.
     */
    public function store(ConfirmedTwoFactorRequest $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $confirm($request->user(), $request->input('code'));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
