<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
        ];

        if ($this->isMethod(FormRequest::METHOD_POST)) {
            $rules['name'][] = 'unique:teams,name';
            $rules['type'][] = 'nullable';
        }
    }
}
