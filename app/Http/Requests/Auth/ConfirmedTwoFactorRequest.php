<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConfirmedTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()->hasEnabledTwoFactorAuthentication();
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'digits:6',
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, __('2fa.already-confirmed'));
    }

    public function bodyParameters()
    {
        return [
            'code' => [
                'description' => '6-digit verification code for two-factor authentication',
                'example' => '123456',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
