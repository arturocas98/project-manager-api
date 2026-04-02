<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class LocateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name_provinces' => $this->name_provinces,
            'name_canton' => $this->name_canton,
        ];
    }
}
