<?php

namespace App\Http\Requests\App;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')
                    ->ignore($this->route('project')?->id), // Esto funciona para store y update
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000', // Añadido límite máximo para descripción
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser texto válido',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'name.unique' => 'Ya existe un proyecto con ese nombre',
            'name.required' => 'Se necesita el nombre para el proyecto',

            'description.string' => 'La descripción debe ser texto válido',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del proyecto',
            'description' => 'descripción',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en el proyecto',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Validaciones adicionales después de las reglas principales
            if ($this->name && strlen($this->name) < 3) {
                $validator->errors()->add(
                    'name',
                    'El nombre del proyecto debe tener al menos 3 caracteres si se proporciona'
                );
            }
        });
    }

    /**
     * Get data to be validated from the request.
     */
    public function validationData(): array
    {
        // Filtra campos nulos o vacíos si es necesario
        $data = parent::validationData();

        // Elimina campos vacíos si son null o strings vacíos
        return array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'Project name',
                'example' => 'E-commerce Platform',
                'required' => true,
                'type' => 'string',
            ],
            'description' => [
                'description' => 'Detailed description of the project',
                'example' => 'Online store with payment gateway and inventory management',
                'required' => false,
                'type' => 'string',
            ],
        ];
    }
}
