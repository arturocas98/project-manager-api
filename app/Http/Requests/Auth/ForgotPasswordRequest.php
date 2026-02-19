<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
            ],
        ];
    }

    public function bodyParameters()
    {
        return [
            'email' => [
                'description' => 'Email address of the user requesting password reset',
                'example' => 'john.doe@example.com',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
