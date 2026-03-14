<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    protected $fillable = [
        'name',
        'created_by_id',
        'type',
    ];

    // En app/Models/Team.php
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->using(TeamUser::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
