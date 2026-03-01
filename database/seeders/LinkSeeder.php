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
                'icon' => 'pi pi-eye',
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
                'icon' => 'pi pi-inbox',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ],
            [
                'id' => 4,
                'type' => MenuType::Link->value,
                'name' => 'Inicio',
                'route' => 'pages/dashboard',
                'icon' => 'pi pi-inbox',
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
            ]
        ], 'id');
    }
}
