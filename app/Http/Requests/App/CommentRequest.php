<?php

namespace App\Http\Requests\App;

use App\Models\ProjectUser;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'El el contenido del comentario es obligatorio',
            'description.string' => 'La descripción debe ser un texto válido',
        ];
    }

    public function bodyParameters()
    {
        return [
            'description' => [
                'description' => 'Detailed description of the incidence',
                'example' => 'The login form does not validate email format correctly',
                'required' => false,
                'type' => 'string',
            ],
        ];
    }
}
