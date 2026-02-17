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
            'role_type' => 'required|string|in:Administrators,Developers,Users,bug,subtask'
        ];
    }

    public function messages(): array
    {
        return [
            'role_type.required' => 'El nuevo rol es obligatorio',
            'role_type.in' => 'Rol inválido. Roles permitidos: Administrators, Developers, Users, bug, subtask'
        ];
    }
}
