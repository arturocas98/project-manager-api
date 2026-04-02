<?php

namespace Database\Seeders;

use App\Enums\MenuType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('links')->exists()) {
            return;
        }

        $now = now()->toDateTimeString();
        DB::table('links')->upsert([
            [
                'id' => 1,
                'type' => MenuType::Link->value,
                'name' => 'Roles',
                'route' => 'account-management/roles/list',
                'icon' => 'ph ph-lock-simple',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 2,
                'type' => MenuType::Link->value,
                'name' => 'Colaboradores',
                'route' => 'account-management/users/list',
                'icon' => 'ph ph-users',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 3,
                'type' => MenuType::Link->value,
                'name' => 'Menu',
                'route' => 'configuration/menu',
                'icon' => 'ph ph-list',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 4,
                'type' => MenuType::Link->value,
                'name' => 'Inicio',
                'route' => 'pages/dashboard',
                'icon' => 'ph ph-house',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 5,
                'type' => MenuType::Link->value,
                'name' => 'Proyectos',
                'route' => 'project-management/projects/list',
                'icon' => 'ph ph-rocket-launch',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 6,
                'type' => MenuType::Link->value,
                'name' => 'Equipos',
                'route' => 'account-management/teams',
                'icon' => 'ph ph-users-three',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 7,
                'type' => MenuType::Link->value,
                'name' => 'Clientes',
                'route' => 'account-management/client',
                'icon' => 'ph ph-buildings',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
        ], ['id']);
    }
}
