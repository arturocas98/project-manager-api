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
        public function bodyParameters()
        {
            return [
                'current_password' => [
                    'description' => 'Contraseña actual del usuario (requerida si se envía nueva contraseña)',
                    'example' => 'MiContraseñaActual123',
                    'required' => function() {
                        return $this->has('password') ? 'required' : 'sometimes';
                    },
                    'type' => 'string',
                ],
                'password' => [
                    'description' => 'Nueva contraseña - Mínimo 8 caracteres, debe contener mayúsculas, minúsculas, números y símbolos. No debe ser una contraseña comprometida.',
                    'example' => 'NuevaC0ntr@s3ñaSegura',
                    'required' => 'sometimes',
                    'type' => 'string',
                ],
                'password_confirmation' => [
                    'description' => 'Confirmación de la nueva contraseña (requerida si se envía password)',
                    'example' => 'NuevaC0ntr@s3ñaSegura',
                    'required' => function() {
                        return $this->has('password') ? 'required' : 'sometimes';
                    },
                    'type' => 'string',
                ],
                'logout_on_all_devices' => [
                    'description' => 'Si es true, cierra la sesión en todos los dispositivos después del cambio de contraseña',
                    'example' => true,
                    'required' => 'sometimes',
                    'type' => 'boolean',
                ],
            ];
        }
}
