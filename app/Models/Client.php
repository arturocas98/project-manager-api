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
        'locate_id',
        'phone',
    ];

    public function locate()
    {
        return $this->belongsTo(Locate::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
