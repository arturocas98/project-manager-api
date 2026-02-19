<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidenceType extends Model
{
    use HasFactory;

    protected $table = 'incidence_types';

    protected $fillable = [
        'type',
    ];
}
