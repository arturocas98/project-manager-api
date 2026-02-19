<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidenceState extends Model
{
    use HasFactory;
    protected $table = 'incidence_states';

    protected $fillable = [
        'state',
    ];
}
