<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_id',
        'link_id',
        'sequence',
        'parent_menu_item_id',
    ];

    public function link(): HasOne
    {
        return $this->hasOne(Link::class, 'id', 'link_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_menu_item_id', 'id');
    }
}
