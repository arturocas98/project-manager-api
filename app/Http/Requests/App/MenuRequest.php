<?php

namespace App\Http\Requests\App;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Traits\MenuItemValidationTrait;

class MenuRequest extends FormRequest
{
    use MenuItemValidationTrait;

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id'),
                Rule::unique(Menu::class, 'role_id')->ignore($this->route('menu'))
            ],
            'links' => [
                'required',
                'array',
            ],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                $data = $validator->safe();
                $this->validateRecursive($validator, $data->links);
            }
        ];
    }
}
