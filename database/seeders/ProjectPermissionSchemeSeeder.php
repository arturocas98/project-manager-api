<?php

namespace Database\Seeders;

use App\Models\ProjectPermissionScheme;
use Illuminate\Database\Seeder;

class ProjectPermissionSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            'Administrador',
            'Gestor de Proyecto',
            'Miembro del Equipo',
            'Invitado',
            'Supervisor',
            'Colaborador Externo',
            'Propietario',
            'Desarrollador',
            'Tester',
            'Cliente',
        ];

        foreach ($schemes as $scheme) {
            ProjectPermissionScheme::firstOrCreate(
                ['name' => $scheme],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Project permission schemes created successfully!');
        $this->command->info('Total schemes: ' . count($schemes));
    }
}
