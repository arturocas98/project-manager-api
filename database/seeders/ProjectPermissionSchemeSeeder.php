<?php

namespace Database\Seeders;

use App\Models\ProjectPermissionScheme;
use Illuminate\Database\Seeder;

class ProjectPermissionSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            'administrator',
            'leader',
            'developer',
            'tester',
            'documenter'
        ];

        $codes = [
            'ADM',
            'LDR',
            'DEV',
            'TST',
            'DOC'
        ];

        foreach ($schemes as $index => $scheme) {
            ProjectPermissionScheme::firstOrCreate(
                ['name' => $scheme],
                [
                    'code' => $codes[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Project permission schemes created successfully!');
        $this->command->info('Total schemes: '.count($schemes));
    }
}
