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
            'users' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path');
            },
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