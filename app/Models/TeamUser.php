<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamUser extends Pivot
{

    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
