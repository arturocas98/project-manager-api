<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'role_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id', 'id');
    }

    public function delete(): ?bool
    {
        $this->items()->delete();
        return parent::delete();
    }
}
