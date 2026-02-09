<?php

namespace App\Actions\App;

use App\Models\Team;
use Illuminate\Support\Facades\DB;

class SaveTeamAction
{
    public function __invoke(array $input, ?Team $project = null): Team
    {
        return DB::transaction(function () use ($input, $project) {
            $project ??= new Team;
            $project->fill($input)->save();

            return $project;
        });
    }
}
