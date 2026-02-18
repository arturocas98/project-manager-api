<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recent extends Model
{
    protected $table = 'recents';

    protected $fillable = [
        'title',
        'user_id',
        'link',
        'icon',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
