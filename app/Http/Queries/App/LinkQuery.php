<?php

namespace App\Http\Queries\App;

use App\Models\Link;
use Illuminate\Http\Request;
use TeamQ\Datatables\QueryBuilder;

class LinkQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        parent::__construct(Link::query(), $request);

        $this
            ->allowedFilters([
                'name',
            ])
            ->allowedSorts([
                'name',
                'created_at',
            ])
            ->defaultSort('-created_at');
    }
}
