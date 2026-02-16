<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenceLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_incidence_id',
        'target_incidence_id',
        'type',
    ];

    public function incidence(): BelongsTo
    {
        return $this->belongsTo(Incidence::class);
    }
}
