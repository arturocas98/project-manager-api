<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnumResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->value,
            'name' => $this->name,
            'description' => $this->when(
                method_exists($this->resource, 'description'),
                fn() => $this->resource->description(auth()->user()->lang?->value ?? 'es')
            ),
            'data' => $this->when(
                method_exists($this->resource, 'data'),
                fn() => $this->resource->data()
            ),
        ];
    }
}
