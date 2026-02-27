<?php

namespace App\Http\Queries\Auth;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\Datatables\QueryBuilder;

class RoleQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = Role::query();

        if (auth()->user() && !auth()->user()->hasRole(RoleName::Admin->value)) {
            $query->where('name', '<>', RoleName::Admin->value);
        }

        parent::__construct($query, $request);

        $this
            ->allowedFilters([
                'name',
                'locked_to_modify',
                'parent_id',
                AllowedFilter::exact('id')
            ])
            ->allowedSorts([
                'name',
                'created_at',
            ])
            ->allowedIncludes([
                'parent'
            ])
            ->defaultSort('-created_at');
    }
}
