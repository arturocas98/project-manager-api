<?php

namespace App\Http\Requests\App;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class TeamRequest extends FormRequest
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
            'name' => [
                'string',
                'max:255',
                Rule::unique('projects', 'name')
                    ->ignore($this->route('team')),
            ],
            'created_by_id' => [
                'integer',
                Rule::exists('users', 'id'),
            ]
        ];
    }

    public function messages(): array{
        return [
            'name.required' => 'El campo Nombre es obligatorio',
            'name.unique' => 'Ese name ya esta registrado',
            'name.string' => 'Tiene que ser tipo texto',
            'name.max' => 'Ah excedido el maximo de caracteres',

            'created_by_id.required' => 'El campo created_by_id es obligatorio',
            'created_by_id.integer' => 'El campo created_by_id tiene que ser un numero entero',
            'created_by_id.exists' => 'El usuario no existe',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Error al registrarse',
                'errors' => $validator->errors(),
            ],status: 422)
        );
    }

}
