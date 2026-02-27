<?php

namespace App\Http\Requests\Auth;

use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')->ignore($this->route('role')),
            ],
            'locked_to_modify' => [
                'sometimes',
                'boolean'
            ],
            'parent_id' => [
                'nullable',
                'integer'
            ],
        ];
    }
}
