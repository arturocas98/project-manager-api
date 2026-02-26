<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_photo_url' => $this->profile_photo_url,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            // Información adicional opcional (puedes comentar si no la necesitas)
            $this->mergeWhen($request->has('with_other_projects'), [
                'other_projects' => $this->whenLoaded('projectRoles', function() {
                    return $this->projectRoles->map(function($role) {
                        return [
                            'project_id' => $role->project->id,
                            'project_name' => $role->project->name,
                            'role' => $role->type,
                        ];
                    });
                }),
            ]),
        ];
    }
}