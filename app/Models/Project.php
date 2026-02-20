<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'description',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    public function roles(): HasMany
    {
        return $this->hasMany(ProjectRole::class);
    }

    public function projectUsers()
    {
        return $this->hasMany(ProjectUser::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuarios del proyecto (a través de roles)
     */
    public function getUsersAttribute()
    {
        return User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('project_users')
                ->whereIn('project_role_id', $this->roles()->pluck('id'));
        })->get();
    }

    /**
     * Verificar si usuario tiene acceso
     */
    public function hasUserAccess(int $userId): bool
    {
        return $this->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }

    /**
     * Obtener rol de usuario en este proyecto
     */
    public function getUserRoleType(int $userId): ?string
    {
        $projectUser = $this->projectUsers()
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        return $projectUser?->role?->type;
    }

    /**
     * Obtener TODOS los roles de un usuario (por si tiene múltiples)
     */
    public function getUserRoles(int $userId)
    {
        return $this->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->with('permissionScheme.scheme.permissions')
            ->get();
    }

    /**
     * Scope para filtrar proyectos de un usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('roles.users', fn($q) => $q->where('user_id', $userId));
    }
}
