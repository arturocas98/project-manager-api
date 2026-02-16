<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectRole extends Model
{
    protected $table = 'project_roles';

    protected $fillable = [
        'project_id',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    /**
     * Un rol tiene UN esquema de permisos
     */
    public function permissionScheme(): HasOne
    {
        return $this->hasOne(ProjectRolePermission::class, 'project_role_id')
            ->with('scheme');
    }

    /**
     * Obtener todos los permisos atómicos de este rol
     */
    public function getPermissionsAttribute(): array
    {
        return $this->permissionScheme?->scheme?->permissionsList ?? [];
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permissionKey): bool
    {
        return in_array($permissionKey, $this->permissions);
    }
}
