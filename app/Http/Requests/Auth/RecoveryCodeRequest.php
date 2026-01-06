<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecoveryCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->two_factor_secret && $this->user()->two_factor_recovery_codes;
    }

    protected function failedAuthorization(): void
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, __('2fa.not-enabled'));
    }
}
