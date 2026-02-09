<?php

namespace App\Http\Requests\App;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class FriendRequest extends FormRequest
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
            'me_email' => 'required|email',
            Rule::exists('users', 'email'),
            'friend_email' => 'required|email',
            Rule::exists('users', 'email'),
        ];
    }

    public function messages(): array
    {
        return [
            'me_email.required' => 'The email field is required.',
            'me_email.email' => 'the email must be a valid email address',
            'me_email.exists' => 'The email provided does not exist',
            'friend_email.required' => 'The email field is required.',
            'friend_email.email' => 'the email must be a valid email address',
            'friend_email.exists' => 'The email provided does not exist',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'mensaje' => 'Error of add Friend',
                'errors' => $validator->errors(),
            ],status: 422)
        );
    }
}
