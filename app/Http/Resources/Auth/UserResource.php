<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\App\EnumResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'reset_password' => $this->reset_password,
            'lang' => $this->lang,
            'rols' => $this->roles,
            'telephone' => $this->telephone,
            'modality_id' => $this->modality_id,
            'modality' => EnumResource::make($this->modality_id),
            'address' => $this->address,
            'birthdate' => $this->birthdate?->toDateString(),
            'employee_type' => $this->employee_type,
            'title' => $this->title,
            'senescyt_record' => $this->senescyt_record,
            'province' => $this->province,
            'canton' => $this->canton,
            'id_card' => $this->id_card,
            'has_electronic_signature' => $this->has_electronic_signature,
            'administrative_direction' => $this->administrative_direction,
            'administrative_unit' => $this->administrative_unit,
            'entity_ruc' => $this->entity_ruc,
            'entity_name' => $this->entity_name,
            'policies_accepted_at' => $this->policies_accepted_at?->toDateTimeLocalString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'created_at' => $this->created_at ? $this->created_at?->format(config('resources.date_time_format')) : '',
            'deleted_at' => $this->deleted_at ? $this->deleted_at?->format(config('resources.date_time_format')) : '',
            'desactive_at' => $this->desactive_at ? $this->desactive_at?->format(config('resources.date_time_format')) : '',
        ];
    }
}
