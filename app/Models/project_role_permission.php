<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class project_role_permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_permission_scheme_id',
        'project_role_id',
        'project_permission_id',
    ];

    public function project_permission_shceme(): BelongsTo
    {
        return $this->belongsTo(project_permission_scheme::class);
    }
    public function project_role(): BelongsTo
    {
        return $this->belongsTo(project_role::class);
    }
    public function project_permission_id(): BelongsTo
    {
        return $this->belongsTo(project_permission::class);
    }
}
