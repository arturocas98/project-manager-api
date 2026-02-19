<?php

namespace App\Http\Requests\Auth;

use App\Enums\Language;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Traits\PasswordRules;

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
                'max:255'
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
                'string'
            ],
            'status' => [
                'nullable',
                'boolean'
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
