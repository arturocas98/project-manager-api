<?php

namespace App\Actions\App;

use App\Models\Team;
use App\Models\Team_User;
use Illuminate\Support\Facades\DB;

class SaveTeamUserAction
{
    public function __invoke(array $input, ?Team_User $teamUser = null): Team
    {
        return DB::transaction(function () use ($input, $teamUser) {

            $teamUser ??= new Team_User;
            $teamUser->fill($input)->save();

            // devolver el Team, no el pivote
            return $teamUser->team()->first();
        });
    }
}
