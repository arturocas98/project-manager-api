<?php

namespace App\Actions\App;

use App\Models\Team;
use Illuminate\Support\Facades\DB;

class CreateTeamAction
{
    public function execute(array $data, int $createdById): Team
    {
        return DB::transaction(function () use ($data, $createdById) {
            return Team::create([
                'name' => $data['name'],
                'type_code' => $data['type'] ?? 'DEV',
                'created_by_id' => $createdById,
            ]);
        });
    }
}
