<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email ?? ''),
            'name' => strtolower($this->name ?? ''),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ];
    }


    public function messages(): array{
        return [
            'email.required' => 'El campo Email es obligatorio',
            'email.unique' => 'Ese email ya esta registrado',
            'email.email' => 'El campo Email debe ser un email',

            'name.unique' => 'Ese name ya esta registrado',
            'name.required' => 'El campo Nombre es obligatorio',

            'password.required' => 'El campo Password es obligatorio',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'mensaje' => 'Error al registrarse',
                'errors' => $validator->errors(),
            ],status: 422)
        );
    }

    public function ValidatedUser():User{
        $user = User::where('email', $this->email)->first();
        if (!$user || !Hash::check($this->password, $user->password)) {
            throw ValidationException::withMessages([
                "user"=>"Credenciales incorrectas",
            ]);
        }
        return $user;
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'User name',
                'example' => 'john doe',
                'required' => true,
                'type' => 'string',
            ],
            'email' => [
                'description' => 'User email address',
                'example' => 'john.doe@example.com',
                'required' => true,
                'type' => 'string',
            ],
            'password' => [
                'description' => 'User password (minimum 6 characters)',
                'example' => 'secret123',
                'required' => true,
                'type' => 'string',
            ],
        ];
    }
}
