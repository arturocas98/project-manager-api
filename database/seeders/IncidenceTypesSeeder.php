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

        $codes = [
            'EPC',
            'USR',
            'TSK',
            'BUG',
            'SUB',
        ];

        foreach ($types as $index => $type) {
            IncidenceType::firstOrCreate(
                ['type' => $type],
                [
                    'code' => $codes[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Types of incidence created successfully!');
        $this->command->info('Total types: '.count($types));
    }
}
