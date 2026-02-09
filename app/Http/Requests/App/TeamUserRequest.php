<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            Rule::exists('users', 'email'),
            'team_id' => 'required|integer',
            Rule::exists('team', 'team_id'),
        ];
    }
    public function messages(): array
    {
        return [
            'email.email' => 'The email must be a valid email address.',
            'email.exists' => 'The email does not exist.',
        ];
    }
}
