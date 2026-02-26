<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date|before_or_equal:due_date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'incidence_priority_id' => 'required|exists:incidence_priorities,id',
            'incidence_type_id' => 'required|exists:incidence_types,id',
            'incidence_state_id' => 'nullable|exists:incidence_states,id',
            'parent_incidence_id' => [
                'nullable',
                'exists:incidences,id',
                function ($attribute, $value, $fail) {
                    if ($this->incidence_type_id == 1 && !is_null($value)) {
                        $fail('Una incidencia de tipo Epic no puede tener una incidencia padre');
                    }
                    if ($this->incidence_type_id != 1 && is_null($value)) {
                        $fail('Las incidencias que no son de tipo Epic deben tener una incidencia padre');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'title.max' => 'El título no puede exceder los 255 caracteres',
            'incidence_type_id.required' => 'El tipo de incidencia es obligatorio',
            'incidence_type_id.exists' => 'El tipo de incidencia seleccionado no es válido',
            'parent_incidence_id.exists' => 'La incidencia padre seleccionada no es válida',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si no se envía fecha, usar la fecha actual
        if (!$this->has('date')) {
            $this->merge([
                'date' => now()->toDateString(),
            ]);
        }
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
                'required' => true,
                'type' => 'integer',
            ],
            'incidence_type_id' => [
                'description' => 'Type ID of the incidence',
                'example' => 3,
                'required' => true,
                'type' => 'integer',
            ],
            'parent_incidence_id' => [
                'description' => 'Parent incidence ID (required for non-Epic types, must be null for Epic type)',
                'example' => 5,
                'required' => false,
                'type' => 'integer',
            ],
        ];
    }
}
