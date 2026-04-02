<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array|string>
     */
    public function rules(): array
    {
        return [
            'id_card' => ['required', 'digits:10'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_card.required' => 'El campo cedula es obligatorio',
            'password.required' => 'El campo Password es obligatorio',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validators) {
            if ($this->id_card) {
                $user = User::where('id_card', $this->id_card)->first();
                if (! $user) {
                    $validators->errors()->add('id_card', 'La cedula ingresada no existe');
                } elseif (! Hash::check($this->password, $user->password)) {
                    $validators->errors()->add('password', 'El password ingresado es incorrecto');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Login error',
                'errors' => $validator->errors(),
            ], status: 422)
        );
    }

    /**
     * Attempt to obtain a user with the credentials provided.
     *
     * @throws ValidationException
     */
    public function getAuthenticatedUser(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::query()->where('id_card', $this->input('id_card'))->first();

        if (! $user || ! Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'id_card' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'id_card' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('id_card')).'|'.$this->ip());
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'Nombre completo del usuario',
                'example' => 'Juan Pérez',
                'required' => true,
                'type' => 'string',
            ],
            'id_card' => [
                'description' => 'cedula del usuario',
                'example' => '0956325427',
                'required' => true,
                'type' => 'string',
            ],
            'password' => [
                'description' => 'Contraseña (mínimo 8 caracteres)',
                'example' => 'secreto123',
                'required' => true,
                'type' => 'string',
            ],
            'password_confirmation' => [
                'description' => 'Confirmación de contraseña',
                'example' => 'secreto123',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
