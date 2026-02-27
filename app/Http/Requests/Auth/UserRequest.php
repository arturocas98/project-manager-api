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
            'rols' => [
                'nullable',
                'array',
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
        }
        // $rules = array_merge($rules, $this->passwordRules());

        return $rules;
    }
}
