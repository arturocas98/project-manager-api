<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class EmailVerificationRequest extends \Illuminate\Foundation\Auth\EmailVerificationRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->setUserResolver(
            fn ($guard) => User::query()->findOr(
                $this->route('id'),
                callback: fn () => throw new InvalidSignatureException()
            )
        );
    }
}
