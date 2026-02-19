<?php

namespace Database\Seeders;

use App\Models\IncidenceState;
use Illuminate\Database\Seeder;

class IncidenceStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            'open',
            'progress',
            'review',
            'closed',
            'locked',
            'finished',
        ];

        foreach ($states as $state) {
            IncidenceState::firstOrCreate(
                ['state' => $state],
            );
        }

        $this->command->info('states of incidence created successfully!');
        $this->command->info('Total states: ' . count($states));
    }
}
