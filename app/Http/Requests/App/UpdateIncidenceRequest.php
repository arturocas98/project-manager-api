<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el servicio/controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'incidence_type_id' => ['sometimes', 'integer', Rule::exists('incidence_types', 'id')],
            'incidence_priority_id' => ['sometimes', 'nullable', 'integer', Rule::exists('incidence_priorities', 'id')],
            'incidence_state_id' => ['sometimes', 'integer', Rule::exists('incidence_states', 'id')],
            'parent_incidence_id' => ['sometimes', 'nullable', 'integer', Rule::exists('incidences', 'id')],
            'assigned_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
            'date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.sometimes' => 'El título solo debe enviarse cuando se desea actualizar',
            'title.max' => 'El título no puede tener más de 255 caracteres',

            'incidence_type_id.sometimes' => 'El tipo de incidencia solo debe enviarse cuando se desea actualizar',
            'incidence_type_id.exists' => 'El tipo de incidencia seleccionado no existe',

            'incidence_priority_id.exists' => 'La prioridad seleccionada no existe',

            'incidence_state_id.sometimes' => 'El estado solo debe enviarse cuando se desea actualizar',
            'incidence_state_id.exists' => 'El estado seleccionado no existe',

            'parent_incidence_id.exists' => 'La incidencia padre seleccionada no existe',

            'assigned_user_id.exists' => 'El usuario asignado no existe',

            'date.date' => 'La fecha debe tener un formato válido',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar campos vacíos que vienen como strings vacíos
        $this->merge(array_filter($this->all(), function ($value) {
            return $value !== '';
        }));
    }

    /**
     * Get custom attributes for validator errors.
     */

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación personalizada: Si se envía parent_incidence_id,
            // verificar que no sea la misma incidencia
            if ($this->has('parent_incidence_id') && $this->parent_incidence_id == $this->route('incidenceId')) {
                $validator->errors()->add(
                    'parent_incidence_id',
                    'Una incidencia no puede ser padre de sí misma'
                );
            }
        });
    }
    public function bodyParameters()
    {
        return [
            'title' => [
                'description' => 'Incidence title',
                'example' => 'Error in login form validation',
                'required' => true,
                'type' => 'string',
            ],
            'description' => [
                'description' => 'Detailed description of the incidence',
                'example' => 'The login form does not validate email format correctly',
                'required' => false,
                'type' => 'string',
            ],
            'date' => [
                'description' => 'Date when the incidence occurred',
                'example' => '2024-03-15',
                'required' => false,
                'type' => 'date',
            ],
            'incidence_priority_id' => [
                'description' => 'Priority ID of the incidence',
                'example' => 2,
                'required' => false,
                'type' => 'integer',
            ],
            'incidence_type_id' => [
                'description' => 'Type ID of the incidence',
                'example' => 3,
                'required' => true,
                'type' => 'integer',
            ],
            'incidence_state_id' => [
                'description' => 'Current state ID of the incidence',
                'example' => 1,
                'required' => true,
                'type' => 'integer',
            ],
            'parent_incidence_id' => [
                'description' => 'Parent incidence ID (required for non-Epic types, must be null for Epic type)',
                'example' => 5,
                'required' => false,
                'type' => 'integer',
            ],
            'assigned_user_id' => [
                'description' => 'User ID assigned to this incidence',
                'example' => 7,
                'required' => false,
                'type' => 'integer',
            ],
            'priority' => [
                'description' => 'Priority level of the incidence',
                'example' => 'alta',
                'required' => false,
                'type' => 'string',
                'enum' => ['baja', 'media', 'alta', 'critica'],
            ],
        ];
    }
}