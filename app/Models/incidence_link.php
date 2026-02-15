<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class incidence_link extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_incidences_id',
        'target_incidences_id',
        'type',
    ];

    public function incidence(): BelongsTo
    {
        return $this->belongsTo(Incidence::class);
    }
}
