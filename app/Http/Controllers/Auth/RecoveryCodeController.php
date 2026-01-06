<?php

namespace App\Http\Controllers\Auth;

use App\Enums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RecoveryCodeRequest;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

#[Group('Auth')]
#[Subgroup('2FA Settings')]
#[Authenticated]
#[Header(Enums\HeaderName::ConfirmedPasswordToken->value)]
#[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
class RecoveryCodeController extends Controller
{
    /**
     * Get 2FA Recovery Codes
     *
     * Get the two-factor authentication recovery codes for authenticated user.
     */
    #[Response(content: ['...', '...', '...'])]
    public function index(RecoveryCodeRequest $request): JsonResponse
    {
        return new JsonResponse(
            json_decode(decrypt($request->user()->two_factor_recovery_codes), true)
        );
    }

    /**
     * Generate 2FA Recovery Codes
     *
     * Generate a fresh set of two-factor authentication recovery codes.
     */
    #[Response(status: JsonResponse::HTTP_NO_CONTENT)]
    public function store(RecoveryCodeRequest $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $generate($request->user());

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
