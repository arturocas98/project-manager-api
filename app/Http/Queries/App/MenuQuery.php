<?php

namespace App\Http\Queries\App;

use App\Enums\FamilyApplicationStatus;
use App\Enums\RoleEnum;
use App\Models\Menu;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use TeamQ\Datatables\QueryBuilder;

class MenuQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        parent::__construct(
            Menu::query(),
            $request
        );

        $this
            ->allowedFilters([
                'name',
                AllowedFilter::exact('id')
            ])
            ->allowedSorts([
                'name',
                'created_at',
            ])
            ->defaultSort('-created_at');
    }
}
