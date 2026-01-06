<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Notifications\VerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Auth')]
#[Subgroup('Email Verification')]
#[Authenticated]
#[Response(content: ['status' => 'We have sent you a new verification email.'])]
#[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
class EmailVerificationController extends Controller
{
    /**
     * Resend verification email
     *
     * Send a new email verification notification.
     */
    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [__('email-verification.already-been-verified')],
            ]);
        }

        $request->user()->notify(new VerifyEmail);

        return new JsonResponse(['status' => __('email-verification.verification-link-sent')]);
    }

    /**
     * Verify email
     *
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $redirectUri = config('app.frontend_url').'/dashboard?verified=1';

        $request->fulfill();

        return redirect()->intended($redirectUri);
    }
}
