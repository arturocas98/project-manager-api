<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('projects', 'name')
                    ->ignore($this->route('project')),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
