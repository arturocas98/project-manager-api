<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectState extends Model
{
    protected $table = 'project_states';

    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
