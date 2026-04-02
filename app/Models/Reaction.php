<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{

    protected $table = 'reactions';

    protected $fillable = [
        'message_id',
        'emoji',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
