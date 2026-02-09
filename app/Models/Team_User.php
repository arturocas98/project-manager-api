<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team_User extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id',
        'user_id',
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
