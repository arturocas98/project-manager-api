<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function schemePermissions(): HasMany
    {
        return $this->hasMany(SchemePermission::class, 'project_permission_id');
    }
}
