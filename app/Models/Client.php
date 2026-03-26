<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc',
        'name',
        'email',
        'province',
        'canton',
        'phone',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
