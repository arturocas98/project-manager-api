<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'ContractNo' => 'sometimes|string',
            'client_id' => 'sometimes|integer|exists:clients,id',
            'project_type' => 'sometimes|string|max:255',
            'objectContract' => 'nullable|string|max:1000',

            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'required_without:end_date'
            ],

            'administrator_email' => 'sometimes|email|max:255',
            'contracted_company' => 'sometimes|string|max:255',
            'last_phase' => 'sometimes|string|max:255',
        ];
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'El cliente seleccionado no es válido',
            'project_type.max' => 'El tipo de proyecto no puede exceder los 255 caracteres',

            'duration_days.required_without' => 'Debe proporcionar duration_days si no existe end_date',
            'duration_days.min' => 'La duración debe ser al menos de 1 día',

            'end_date.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a start_date',
        ];
    }

    /**
     * Documentación API
     */
    public function bodyParameters()
    {
        return [
            'ContractNo' => [
                'description' => 'Number of contract',
                'example' => 'ERMNT-DC-003-2022',
                'required' => false,
                'type' => 'string',
            ],
            'client_id' => [
                'description' => 'Project client ID',
                'example' => 1,
                'required' => false,
                'type' => 'integer',
            ],
            'project_type' => [
                'description' => 'Type of project',
                'example' => 'Infraestructura',
                'required' => false,
                'type' => 'string',
            ],
            'objectContract' => [
                'description' => 'Subject of the Contract',
                'example' => 'Construcción de planta industrial',
                'required' => false,
                'type' => 'string',
            ],
            'start_date' => [
                'description' => 'Project start date',
                'example' => '2026-03-10',
                'required' => false,
                'type' => 'date',
            ],
            'end_date' => [
                'description' => 'Project end date',
                'example' => '2027-03-10',
                'required' => false,
                'type' => 'date',
            ],
            'duration_days' => [
                'description' => 'Duration of the project in days',
                'example' => 365,
                'required' => false,
                'type' => 'integer',
            ],
            'administrator_email' => [
                'description' => 'Administrator email',
                'example' => 'admin@empresa.com',
                'required' => false,
                'type' => 'string',
            ],
            'contracted_company' => [
                'description' => 'Contracted company',
                'example' => 'Constructora ABC',
                'required' => false,
                'type' => 'string',
            ],
            'last_phase' => [
                'description' => 'Current project phase',
                'example' => 'Fase II - Construcción',
                'required' => false,
                'type' => 'string',
            ],
        ];
    }
}