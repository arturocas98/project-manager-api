<?php

namespace App\Http\Resources\Auth;

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
            'address' => $this->address,
            'policies_accepted_at' => $this->policies_accepted_at?->toDateTimeLocalString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'created_at' => $this->created_at ? $this->created_at?->format(config('resources.date_time_format')) : '',
            'deleted_at' => $this->deleted_at ? $this->deleted_at?->format(config('resources.date_time_format')) : '',
            'desactive_at' => $this->desactive_at ? $this->desactive_at?->format(config('resources.date_time_format')) : '',
        ];
    }
}
