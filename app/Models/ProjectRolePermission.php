<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRolePermission extends Model
{
    use HasFactory;
    protected $table = 'project_role_permissions';

    protected $fillable = [
        'permission_scheme_id',
        'project_role_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id');
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ProjectPermissionScheme::class, 'permission_scheme_id');
    }
}
