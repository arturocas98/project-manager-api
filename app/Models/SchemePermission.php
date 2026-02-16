<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_scheme_id',
        'project_permission_id',
    ];
    public function projectPermission(): BelongsTo
    {
        return $this->belongsTo(ProjectPermission::class);
    }
    public function permissionScheme(): BelongsTo
    {
        return $this->belongsTo(ProjectPermissionScheme::class);
    }
}
