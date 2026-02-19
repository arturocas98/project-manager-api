<?php

namespace Database\Seeders;

use App\Models\ProjectPermissionScheme;
use Illuminate\Database\Seeder;

class ProjectPermissionSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            'administrators',
            'gestor de Proyecto',
            'miembro del Equipo',
            'invitado',
            'supervisor',
            'colaborador Externo',
            'propietario',
            'desarrollador',
            'tester',
            'cliente',
        ];

        foreach ($schemes as $scheme) {
            ProjectPermissionScheme::firstOrCreate(
                ['name' => $scheme],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Project permission schemes created successfully!');
        $this->command->info('Total schemes: '.count($schemes));
    }
}
