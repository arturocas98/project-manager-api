<?php

namespace App\Http\Requests\App;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ContractNo' => $this->ContractNo ? trim($this->ContractNo) : null,
            'objectContract' => $this->objectContract ? trim($this->objectContract) : null,
            'client' => $this->client ? trim($this->client) : null,
            'project_type' => $this->project_type ? trim($this->project_type) : null,
            'administrator_email' => $this->administrator_email ? trim($this->administrator_email) : null,
            'contracted_company' => $this->contracted_company ? trim($this->contracted_company) : null,
            'last_phase' => $this->last_phase ? trim($this->last_phase) : null,
            'project_state_id' => $this->project_state_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'ContractNo' => [
                'required',
                'string',
            ],

            'client' => [
                'required',
                'string',
                'max:255'
            ],

            'project_type' => [
                'required',
                'string',
                'max:255'
            ],

            'start_date' => [
                'required',
                'date'
            ],

            'duration_days' => [
                'nullable',
                'integer',
                'min:1',
                'required_without:end_date'
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'required_without:duration_days'
            ],

            'administrator_email' => [
                'nullable',
                'email',
                'max:255'
            ],

            'contracted_company' => [
                'nullable',
                'string',
                'max:255'
            ],

            'last_phase' => [
                'nullable',
                'string',
                'max:255'
            ],

            'project_state_id' => [
                'required',
                'exists:project_states,id'
            ],

            'objectContract' => [
                'nullable',
                'string',
                'max:2000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ContractNo.required' => 'El numero de contrato es obligatorio',

            'client.required' => 'El cliente es obligatorio',

            'project_type.required' => 'El tipo de proyecto es obligatorio',

            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.date' => 'La fecha de inicio no es válida',

            'duration_days.integer' => 'El plazo debe ser un número',

            'end_date.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio',

            'administrator_email.email' => 'El correo del administrador no es válido',
        ];
    }

    public function attributes(): array
    {
        return [
            'ContractNo' => 'numero de contrato',
            'client' => 'cliente',
            'project_type' => 'tipo de proyecto',
            'start_date' => 'fecha de inicio',
            'duration_days' => 'plazo',
            'end_date' => 'fecha de finalización',
            'administrator_email' => 'correo del administrador',
            'contracted_company' => 'empresa contratada',
            'last_phase' => 'última fase',
            'project_state_id' => 'estado',
            'objectContract' => 'objeto del contrato',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación en el proyecto',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}