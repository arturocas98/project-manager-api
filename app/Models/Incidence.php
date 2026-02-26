<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidence extends Model
{
    use HasFactory;

    protected $table = 'incidences';

    protected $fillable = [
        'title',
        'project_id',
        'incidence_type_id',
        'incidence_priority_id',
        'incidence_state_id',
        'parent_incidence_id',
        'created_by_id',
        'assigned_user_id',
        'description',
        'due_date',
        'start_date',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
    // En el modelo Incidence
    protected $appends = ['assigned_user_role'];

    public function getAssignedUserRoleAttribute()
    {
        if (!$this->assigned_user_id || !$this->project_id) {
            return null;
        }

        return $this->assignedUser?->projectRoles()
            ->whereHas('project', fn($q) => $q->where('id', $this->project_id))
            ->first()
            ?->name;
    }
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function incidenceType():BelongsTo
    {
        return $this->belongsTo(IncidenceType::class,  'incidence_type_id');
    }
    public function incidenceState():BelongsTo
    {
        return $this->belongsTo(IncidenceState::class, 'incidence_state_id');
    }
    public function parentIncidence():BelongsTo
    {
        return $this->belongsTo(Incidence::class, 'parent_incidence_id');
    }
    public function childIncidences(): HasMany
    {
        return $this->hasMany(Incidence::class, 'parent_incidence_id');
    }

    public function incidencePriority(): BelongsTo
    {
        return $this->belongsTo(IncidencePriority::class, 'incidence_priority_id');
    }
}
