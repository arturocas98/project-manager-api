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
            'type' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
        ];
    }
}
