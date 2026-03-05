<?php

namespace Database\Seeders;

use App\Models\IncidenceCategory;
use Illuminate\Database\Seeder;

class IncidenceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Desarrollo',
            'Diseño',
            'Documentación',
            'Pruebas',
            'Despliegue',
            'Capacitación',
            'Soporte/Validación',
            'Corrección de errores',
            'Reunión/Coordinación   ',
            'Migración',
        ];

        $codes = [
            'DES',
            'DIS',
            'DOC',
            'PRU',
            'DEP',
            'CAP',
            'SOV',
            'BUG',
            'REU',
            'MIG',
        ];

        foreach ($categories as $index => $category) {
            IncidenceCategory::firstOrCreate(
                ['name' => $category],
                [
                    'code' => $codes[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('categories of incidence created successfully!');
        $this->command->info('Total categories: '.count($categories));
    }
}
