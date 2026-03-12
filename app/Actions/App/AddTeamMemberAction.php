<?php

namespace App\Actions\App;

use App\Models\TeamUser;
use Illuminate\Support\Facades\DB;

class AddTeamMemberAction
{
    public function execute(int $teamId, int $userId): TeamUser
    {
        return DB::transaction(function () use ($teamId, $userId) {
            // Verificar si ya existe la relación
            $exists = TeamUser::where('team_id', $teamId)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                throw new \Exception('El usuario ya es miembro de este equipo');
            }

            // Crear la relación en team_user
            return TeamUser::create([
                'team_id' => $teamId,
                'user_id' => $userId,
            ]);
        });
    }
}