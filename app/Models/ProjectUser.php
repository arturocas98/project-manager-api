<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_role_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
