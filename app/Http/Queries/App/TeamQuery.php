<?php

namespace App\Http\Queries\App;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use TeamQ\Datatables\QueryBuilder;

class TeamQuery extends QueryBuilder
{

    public function __construct(Request $request)
    {
        parent::__construct(
            Team::query(),
            $request
        );
        $this
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts([
                AllowedSort::field('name'),
                AllowedSort::field('created_at'),
            ])
            ->defaultSort(
                AllowedSort::field('-created_at')
            );
    }
}
