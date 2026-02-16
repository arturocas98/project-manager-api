<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPermissionScheme extends Model
{

    protected $table = 'project_permission_schemes';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            ProjectPermission::class,
            'scheme_permissions',
            'permission_scheme_id',
            'project_permission_id'
        )->withTimestamps();
    }

    public function schemePermissions(): HasMany
    {
        return $this->hasMany(SchemePermission::class, 'permission_scheme_id');
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(ProjectRolePermission::class, 'permission_scheme_id');
    }

    /**
     * Helper para obtener array de permisos
     */
    public function getPermissionsListAttribute(): array
    {
        return $this->permissions()->pluck('key')->toArray();
    }
}
