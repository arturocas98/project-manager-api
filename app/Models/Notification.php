<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected  $table = 'notifications';
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'read',
        'link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
