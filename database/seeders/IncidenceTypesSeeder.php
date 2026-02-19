<?php

namespace Database\Seeders;

use App\Models\IncidenceType;
use Illuminate\Database\Seeder;

class IncidenceTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'epic',
            'history_user',
            'task',
            'bug',
            'subtask',
        ];

        foreach ($types as $type) {
            IncidenceType::firstOrCreate(
                ['type' => $type],
            );
        }

        $this->command->info('Types of incidence created successfully!');
        $this->command->info('Total types: ' . count($types));
    }
}
