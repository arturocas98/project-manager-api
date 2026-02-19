<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmablePasswordRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'current_password',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es requerida para confirmar esta acción',
            'current_password.current_password' => 'La contraseña ingresada no es correcta',
        ];
    }

    public function bodyParameters()
    {
        return [
            'current_password' => [
                'description' => 'Contraseña actual del usuario para confirmar la acción sensible. ' .
                    'Este endpoint requiere la confirmación de la contraseña antes de realizar ' .
                    'operaciones críticas como eliminar cuenta, cambiar email, o acceder a áreas sensibles.',
                'example' => 'MiContraseñaActual_2024',
                'required' => 'required',
                'type' => 'string',
            ],
        ];
    }
}
