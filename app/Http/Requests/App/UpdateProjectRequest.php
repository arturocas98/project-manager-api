<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización se maneja por middleware, pero dejamos true
        return true;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'key' => 'sometimes|string|max:10|unique:projects,key,' . $this->route('project'),
        ];
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'key.unique' => 'La clave del proyecto ya está en uso por otro proyecto',
            'key.max' => 'La clave no puede exceder los 10 caracteres',
        ];
    }

    /**
     * Preparar los datos para la validación
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('key')) {
            $this->merge([
                'key' => strtoupper($this->key)
            ]);
        }
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'Project name',
                'example' => 'E-commerce Platform',
                'required' => false,
                'type' => 'string',
            ],
            'description' => [
                'description' => 'Detailed description of the project',
                'example' => 'Online store with payment gateway and inventory management',
                'required' => false,
                'type' => 'string',
            ],
            'key' => [
                'description' => 'Unique project key (max 10 characters)',
                'example' => 'ECOMM',
                'required' => false,
                'type' => 'string',
            ],
        ];
    }
}
