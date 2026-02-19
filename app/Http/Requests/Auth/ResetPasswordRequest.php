<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
            ],

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->symbols()
                    ->numbers()
                    ->letters()
                    ->uncompromised(),
            ],
        ];
    }
    public function bodyParameters()
    {
        return [
            'token' => [
                'description' => 'Password reset token received via email',
                'example' => 'reset_token_123456',
                'required' => true,
                'type' => 'string',
            ],
            'email' => [
                'description' => 'Email address of the user',
                'example' => 'john.doe@example.com',
                'required' => true,
                'type' => 'string',
            ],
            'password' => [
                'description' => 'New password (minimum 8 characters, must contain mixed case, numbers, symbols, and letters)',
                'example' => 'NewSecurePass123!',
                'required' => true,
                'type' => 'string',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation (must match password)',
                'example' => 'NewSecurePass123!',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
