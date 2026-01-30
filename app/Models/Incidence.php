<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'project_id',
        'incidence_type_id',
        'incidence_state_id',
        'parent_incidence_id',
        'created_by_id',
        'assigned_user_id',
        'description',
        'date',
        'priority',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function incidenceType():BelongsTo
    {
        return $this->belongsTo(Incidence_type::class,  'incidence_type_id');
    }
    public function incidenceState():BelongsTo
    {
        return $this->belongsTo(Incidence_state::class, 'incidence_state_id');
    }
    public function parentIncidence():BelongsTo
    {
        return $this->belongsTo(Incidence::class, 'parent_incidence_id');
    }
    public function childIncidences(): HasMany
    {
        return $this->hasMany(Incidence::class, 'parent_incidence_id');
    }
}
