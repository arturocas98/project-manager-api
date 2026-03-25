<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->user()->id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'imagen_online' => 'nullable|string',
            'alert' => 'nullable|boolean',
            'message_id' => 'nullable|exists:messages,id',
        ];
    }
}
