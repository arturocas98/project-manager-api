<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemePermission extends Model
{
    use HasFactory;
    protected $table = 'scheme_permissions';

    protected $fillable = [
        'permission_scheme_id',
        'project_permission_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ProjectPermissionScheme::class, 'permission_scheme_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(ProjectPermission::class, 'project_permission_id');
    }
}
