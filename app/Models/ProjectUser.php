<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_role_id',
        'user_id',
    ];

    public function projectRole(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class);
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
