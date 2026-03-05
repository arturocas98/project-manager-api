<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;


class AddProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'role_code' => 'required|string|in:ADM,LDR,DEV,TST,DOC',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es obligatorio',
            'user_id.exists' => 'El usuario no existe en el sistema',
            'role_code.required' => 'El rol es obligatorio',
            'role_code.in' => 'Rol inválido. Roles permitidos: ADM (Administrador), LDR (Líder), DEV (Desarrollador), TST (Tester), DOC (Documentador).',
        ];
    }

    public function bodyParameters()
    {
        return [
            'user_id' => [
                'description' => 'ID del usuario que se añadirá al proyecto',
                'example' => 5,
                'required' => true,
                'type' => 'integer',
            ],
            'role_code' => [
                'description' => 'Código del rol que tendrá el usuario en el proyecto',
                'example' => 'DEV',
                'required' => true,
                'type' => 'string',
                'enum' => ['ADM', 'LDR', 'DEV', 'TST', 'DOC'],
            ],
        ];
    }
}