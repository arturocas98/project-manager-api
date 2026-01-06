<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => [
                'required_with:password',
                'current_password',
            ],

            'password' => [
                'sometimes',
                'max:16',
                Password::min(8)
                    ->mixedCase()
                    ->symbols()
                    ->numbers()
                    ->letters()
                    ->uncompromised(),
                'confirmed',
            ],

            'logout_on_all_devices' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
