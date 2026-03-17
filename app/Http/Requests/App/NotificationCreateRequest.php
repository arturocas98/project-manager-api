<?php

namespace App\Http\Requests\App;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class  NotificationCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id'),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'link' => [
                'required',
                'string',
                'max:255',
            ],
            'message' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
