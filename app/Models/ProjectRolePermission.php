<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_scheme_id',
        'project_role_id',
    ];

    public function permissionScheme(): BelongsTo
    {
        return $this->belongsTo(ProjectPermissionScheme::class);
    }
    public function projectRole(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class);
    }
}
