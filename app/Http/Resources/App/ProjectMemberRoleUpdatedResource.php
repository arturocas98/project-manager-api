<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMemberRoleUpdatedResource extends JsonResource
{
    public function toArray($request)
    {
        return (new ProjectMemberResource($this->resource['assignment']))->toArray($request);
    }
}