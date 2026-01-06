<?php

namespace App\Http\Controllers\Auth;

use App\Enums;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Group('Auth')]
#[Subgroup('2FA Settings')]
#[Authenticated]
#[Header(Enums\HeaderName::ConfirmedPasswordToken->value)]
#[Response(status: JsonResponse::HTTP_NO_CONTENT)]
#[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
#[ResponseFromFile(file: 'responses/423.json', status: JsonResponse::HTTP_LOCKED)]
class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable 2FA
     *
     * Enable two-factor authentication for the user.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        if ($request->user()->hasEnabledTwoFactorAuthentication()) {
            throw new HttpException(JsonResponse::HTTP_FORBIDDEN, __('2fa.already-confirmed'));
        }

        $enable($request->user());

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Disable 2FA
     *
     * Disable two-factor authentication for the user.
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            throw new HttpException(JsonResponse::HTTP_FORBIDDEN, __('2fa.not-enabled'));
        }

        $disable($request->user());

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
