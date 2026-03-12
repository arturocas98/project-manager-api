<?php

namespace App\Actions\App;

use App\Models\TeamUser;

class RemoveTeamMemberAction
{
    public function execute(int $teamId, int $userId): bool
    {
        return TeamUser::where('team_id', $teamId)
                ->where('user_id', $userId)
                ->delete() > 0;
    }
}