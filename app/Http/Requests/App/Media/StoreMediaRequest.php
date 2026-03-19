<?php

namespace App\Http\Requests\App\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
            'file' => ['required', 'file'],
            'collection_name' => ['nullable', 'string'],
        ];
    }
}
