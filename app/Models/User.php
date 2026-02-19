<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasPermissions;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Permissions only exist in the context of the API.
     */
    protected function getDefaultGuardName(): string
    {
        return 'api';
    }

    public function projectRoles()
    {
        return $this->belongsToMany(ProjectRole::class, 'project_users')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function projects()
    {
        return Project::whereHas('roles.users', fn ($q) => $q->where('user_id', $this->id));
    }

    /**
     * Verificar si tiene acceso a un proyecto
     */
    public function hasProjectAccess(int $projectId): bool
    {
        return $this->projectRoles()
            ->whereHas('project', fn ($q) => $q->where('id', $projectId))
            ->exists();
    }

    /**
     * Obtener el rol en un proyecto específico
     */
    public function getProjectRole(int $projectId): ?ProjectRole
    {
        return $this->projectRoles()
            ->whereHas('project', fn ($q) => $q->where('id', $projectId))
            ->first();
    }

    /**
     * Verificar si tiene un permiso específico en un proyecto
     */
    public function hasProjectPermission(int $projectId, string $permissionKey): bool
    {
        $role = $this->getProjectRole($projectId);

        if (! $role) {
            return false;
        }

        // Cargar la relación si no está cargada
        if (! $role->relationLoaded('permissionScheme')) {
            $role->load('permissionScheme.scheme.permissions');
        }

        return $role->hasPermission($permissionKey);
    }
}
