<?php

namespace App\Actions\App;

use App\Models\Incidence;
use Illuminate\Support\Facades\DB;

class SaveIncidenceAction
{
    public function __invoke(array $input, ?Incidence $project = null): Incidence
    {
        return DB::transaction(function () use ($input, $project) {
            $project ??= new Incidence;
            $project->fill($input)->save();

            return $project;
        });
    }
}
