<?php

namespace App\Traits;

use Illuminate\Validation\Rules\Password;

trait PasswordRules
{
    protected function passwordRules(): array
    {
        return [
            'password' => [
                'sometimes',
                'string',
                'max:16',
                Password::min(8)
                    ->mixedCase()
                    ->symbols()
                    ->numbers()
                    ->letters()
                    ->uncompromised(),
            ],
        ];
    }
}
