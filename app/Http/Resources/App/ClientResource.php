<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Ruc' => $this->Ruc,
            'Nombre' => $this->Nombre,
            'Correo' => $this->Correo,
            'Provincia' => $this->Provincia,
            'Canton' => $this->Canton,
            'Telefono' => $this->Telefono,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
