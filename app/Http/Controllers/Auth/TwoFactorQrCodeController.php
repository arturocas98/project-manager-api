<?php

namespace App\Http\Controllers\Auth;

use App\Enums;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Group('Auth')]
#[Subgroup('2FA Settings')]
#[Authenticated]
#[Header(Enums\HeaderName::ConfirmedPasswordToken->value)]
#[Response(content: ['svg' => '<svg>...</svg>', 'url' => 'otp://...'])]
#[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
class TwoFactorQrCodeController extends Controller
{
    /**
     * Show 2FA QR Code
     *
     * Get the SVG element for the user's two-factor authentication QR code.
     */
    public function show(Request $request): JsonResponse
    {
        if (is_null($request->user()->two_factor_secret)) {
            throw new HttpException(JsonResponse::HTTP_FORBIDDEN, __('2fa.not-enabled'));
        }

        if (! is_null($request->user()->two_factor_confirmed_at)) {
            throw new HttpException(JsonResponse::HTTP_FORBIDDEN, __('2fa.already-confirmed'));
        }

        return new JsonResponse([
            'svg' => $request->user()->twoFactorQrCodeSvg(),
            'url' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }
}
