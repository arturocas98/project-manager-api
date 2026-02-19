<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardProject extends Model
{
    use HasFactory;

    protected $table = 'board_projects';

    protected $fillable = [
        'project_id',
        'board_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
