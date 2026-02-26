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
            'role_type' => 'required|string|in:administrators,developer'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es obligatorio',
            'user_id.exists' => 'El usuario no existe en el sistema',
            'role_type.required' => 'El rol es obligatorio',
            'role_type.in' => 'Rol inválido. Roles permitidos: Administrators, Developers, Users, bug, subtask'
        ];
    }

    public function bodyParameters()
    {
        return [
            'user_id' => [
                'description' => 'ID of the user to be added to the project',
                'example' => 5,
                'required' => true,
                'type' => 'integer',
            ],
            'role_type' => [
                'description' => 'Role that the user will have in the project',
                'example' => 'Developers',
                'required' => true,
                'type' => 'string',
                'enum' => ['administrators', 'developers', 'users'],
            ],
        ];
    }
}
