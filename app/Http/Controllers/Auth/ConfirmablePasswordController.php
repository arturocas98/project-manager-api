<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\ConfirmablePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Auth')]
#[Subgroup('Password Confirmation')]
#[Authenticated]
#[Response(content: ['confirmed_password_token' => '...'])]
#[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
class ConfirmablePasswordController extends Controller
{
    /**
     * Confirm password
     *
     * Confirm the user's password.
     * <aside class="notice">⚠️ This endpoint only allows 3 requests per minute.</aside>
     */
    public function store(ConfirmablePasswordRequest $request): JsonResponse
    {
        return new JsonResponse([
            'confirmed_password_token' => Crypt::encryptString($request->user()->getKey().'|'.time()),
        ]);
    }
}
