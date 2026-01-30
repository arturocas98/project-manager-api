<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidence_type extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
    ];

}
