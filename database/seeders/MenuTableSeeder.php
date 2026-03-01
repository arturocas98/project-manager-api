<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class MenuTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('menus')->exists()) {
            return;
        }

        $roles = Role::query()->get();

        $now = now()->toDateTimeString();

        DB::table('menus')->upsert([
            [
                'id' => 1,
                'name' => 'Menu de Administrador',
                'role_id' => $roles->firstWhere('name', RoleName::Admin->value)?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Menu de Director de proyectos',
                'role_id' => $roles->firstWhere('name', RoleName::ProjectManager->value)?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Menu de Colaborador',
                'role_id' => $roles->firstWhere('name', RoleName::Collaborator->value)?->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], 'id');
    }
}
