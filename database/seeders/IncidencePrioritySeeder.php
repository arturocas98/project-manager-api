<?php

namespace Database\Seeders;

use App\Models\IncidencePriority;
use Illuminate\Database\Seeder;

class IncidencePrioritySeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            'low',
            'medium',
            'high',
            'critical',
        ];

        foreach ($states as $state) {
            IncidencePriority::firstOrCreate(
                ['priority' => $state],
            );
        }

        $this->command->info('priorities of incidence created successfully!');
        $this->command->info('Total states: '.count($states));
    }
}
