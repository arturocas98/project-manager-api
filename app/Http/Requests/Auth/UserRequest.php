<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserModality;
use App\Models\User;
use App\Traits\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    use PasswordRules;

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'id_card' => [
                'nullable',
                'digits:10',
                'unique:users,id_card'
            ],
            'role_id' => [
                'nullable',
                'exists:roles,id'
            ],
            'rols' => [
                'required',
                'array',
            ],
            'rols.*.name' => [
                'required',
                'string'
            ],
            'status' => [
                'nullable',
                'boolean',
            ],
            'telephone' => [
                'nullable',
                'string'
            ],
            'modality_id' => [
                'nullable',
                'int',
                // Rule::enum(UserModality::cases())
            ],
            'address' => [
                'nullable',
                'string'
            ],
            'birthdate' => [
                'nullable',
                'date'
            ],
            'employee_type' => [
                'nullable',
                'string'
            ],
            'title' => [
                'nullable',
                'string'
            ],
            'senescyt_record' => [
                'nullable',
                'string'
            ],
            'locate_id' => [
                'nullable',
                'exists:locates,id'
            ],
            'has_electronic_signature' => [
                'nullable',
                'boolean'
            ],
            'administrative_direction' => [
                'nullable',
                'string'
            ],
            'administrative_unit' => [
                'nullable',
                'string'
            ],
            'entity_ruc' => [
                'nullable',
                'string'
            ],
            'entity_name' => [
                'nullable',
                'string'
            ],
        ];
        if ($this->isMethod(FormRequest::METHOD_POST)) {
            $rules['email'][] = Rule::unique(User::class, 'email')->withoutTrashed();
            $rules['id_card'][] = Rule::unique(User::class, 'id_card')->withoutTrashed();
        } else
            $rules['password'][] = 'nullable';
        return $rules;
    }
}
