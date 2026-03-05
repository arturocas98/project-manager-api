<?php

namespace Database\Seeders;

use App\Models\IncidenceState;
use Illuminate\Database\Seeder;

class IncidenceStateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            'Asignado',
            'Ejecutando',
            'Suspendido',
            'Terminada',
            'Terminada (fuera de plazo)',
            'En Revisión',
            'Finalizada',
        ];

        $codes = [
            'ASG',
            'EJE',
            'SUS',
            'TER',
            'TER-T',
            'REV',
            'FIN',
        ];

        $colors = [
            'D6E4F0',
            'D9E2F3',
            'FFF2CC',
            'C6EFCE',
            'F2DCDB',
            'FFF9C4',
            'DAEEF3',
        ];

        foreach ($states as $index => $state) {
            IncidenceState::firstOrCreate(
                ['state' => $state],
                [
                    'code' => $codes[$index],
                    'color' => $colors[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('states of incidence created successfully!');
        $this->command->info('Total states: '.count($states));
    }
}
