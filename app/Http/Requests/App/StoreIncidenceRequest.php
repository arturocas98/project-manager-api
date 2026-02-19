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
            'date' => 'nullable|date',
            'incidence_priority_id' => 'required|exists:incidence_priorities,id',
            'incidence_type_id' => 'required|exists:incidence_types,id',
            'parent_incidence_id' => [
                'nullable',
                'exists:incidences,id',
                function ($attribute, $value, $fail) {
                    // Si el tipo es Epic (ID: 1), parent_incidence_id debe ser nulo
                    if ($this->incidence_type_id == 1 && ! is_null($value)) {
                        $fail('Una incidencia de tipo Epic no puede tener una incidencia padre');
                    }

                    // Si el tipo no es Epic, parent_incidence_id es obligatorio
                    if ($this->incidence_type_id != 1 && is_null($value)) {
                        $fail('Las incidencias que no son de tipo Epic deben tener una incidencia padre');
                    }
                },
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
        if (! $this->has('date')) {
            $this->merge([
                'date' => now()->toDateString(),
            ]);
        }
    }
}
