<?php

namespace Database\Seeders;

use App\Models\IncidenceCategory;
use App\Models\ProjectState;
use Illuminate\Database\Seeder;

class ProjectStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            'open',
            'process',
            'finish',
            'suspended',
        ];

        foreach ($states as $state) {
            ProjectState::firstOrCreate(
                ['name' => $state],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('categories of incidence created successfully!');
    }
}