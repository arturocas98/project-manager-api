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
                'required',
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
        ];
        if ($this->isMethod(FormRequest::METHOD_POST)) {
            $rules['email'][] = Rule::unique(User::class, 'email')->withoutTrashed();
            $rules['id_card'][] = Rule::unique(User::class, 'id_card')->withoutTrashed();
            $rules = array_merge($rules, $this->passwordRules());
        }

        return $rules;
    }
}
