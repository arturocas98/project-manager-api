<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_provinces',
        'name_canton',
    ];
}
