<?php

namespace App\Http\Requests\Auth;

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
            'reset_password' => [
                'required',
                'boolean',
            ],
            'rols' => [
                'required',
                'array',
            ],
            'rols.*.name' => [
                'required',
                'string',
            ],
            'status' => [
                'nullable',
                'boolean',
            ],
            'expires_at' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d',
                'after:today',
            ],
        ];
        if ($this->isMethod(FormRequest::METHOD_POST)) {
            $rules['email'][] = Rule::unique(User::class, 'email')->withoutTrashed();
        }
        $rules = array_merge($rules, $this->passwordRules());

        return $rules;
    }
}
