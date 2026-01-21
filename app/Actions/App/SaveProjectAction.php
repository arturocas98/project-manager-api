<?php

namespace App\Actions\App;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class SaveProjectAction
{
    public function __invoke(array $input, ?Project $project = null): Project
    {
        return DB::transaction(function () use ($input, $project) {
            $project ??= new Project;
            $project->fill($input)->save();

            return $project;
        });
    }
}
