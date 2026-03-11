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

        $descriptions = [
            'Codificación, implementación de funcionalidades',
            'Diseño UI/UX, maquetación, prototipos',
            'Manuales, informes técnicos, actas',
            'Testing, QA, validación funcional',
            'Instalación, configuración de servidores',
            'Formación a usuarios, talleres',
            'Levantamiento de información, validación en sitio',
            'Bugs, incidencias, hotfixes',
            'Reuniones de seguimiento, planificación',
            'Migración de datos, conversiones',
        ];

        foreach ($categories as $index => $category) {
            IncidenceCategory::firstOrCreate(
                ['name' => $category],
                [
                    'code' => $codes[$index],
                    'description' => $descriptions[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('categories of incidence created successfully!');
        $this->command->info('Total categories: '.count($categories));
    }
}