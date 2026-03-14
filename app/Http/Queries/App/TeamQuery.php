<?php

namespace App\Http\Queries\App;

use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use TeamQ\Datatables\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
class TeamQuery
{
    public function withAllRelations(): Builder
    {
        return Team::with([
            'createdBy' => function ($query) {
                $query->select('id', 'name', 'email');
            },
        ]);
    }

    public function findWithRelations(int $id)
    {
        return $this->withAllRelations()->find($id);
    }
}