<?php

namespace Database\Seeders;

use App\Models\IncidencePriority;
use Illuminate\Database\Seeder;

class IncidencePrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            'low',
            'medium',
            'high',
            'critical',
        ];

        $codes = [
            'BAJ',
            'MED',
            'ALT',
            'CRT',
        ];

        foreach ($priorities as $index => $priority) {
            IncidencePriority::firstOrCreate(
                ['priority' => $priority],
                [
                    'code' => $codes[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('priorities of incidence created successfully!');
        $this->command->info('Total states: '.count($priorities));
    }
}
