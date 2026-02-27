<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_type' => 'required|string|in:administrators,developers,users,bug,subtask,Desarrollador'
        ];
    }

    public function messages(): array
    {
        return [
            'role_type.required' => 'El nuevo rol es obligatorio',
            'role_type.in' => 'Rol inválido. Roles permitidos: Administrators, Developers, Users, bug, subtask',
        ];
    }

    public function bodyParameters()
    {
        return [
            'role_type' => [
                'description' => 'New role for the team member',
                'example' => 'Developers',
                'required' => true,
                'type' => 'string',
                'enum' => ['administrators', 'developers', 'users'],
            ],
        ];
    }
}
