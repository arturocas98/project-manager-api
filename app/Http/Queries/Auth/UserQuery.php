<?php

namespace App\Http\Queries\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\Datatables\Filters\GlobalFilter;
use TeamQ\Datatables\QueryBuilder;

class UserQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        parent::__construct(User::query()->withTrashed(), $request);

        $this
            ->allowedFilters([
                AllowedFilter::custom('global', new GlobalFilter([
                    'id',
                    'name',
                    'agreement_id',
                    'university_id',
                    'email',
                    'roles.name',
                ])),
                'name',
                'agreement_id',
                'university_id',
                'agreement.id',
                AllowedFilter::exact('agreement.institution_id'),
                'roles.name',
                AllowedFilter::exact('id'),
                'email',
            ])
            ->allowedSorts([
                'name',
                'created_at',
            ])
            ->allowedIncludes([
                'familyApplication',
                'agreement',
                'university',
            ])
            ->defaultSort('-created_at');
    }
}
