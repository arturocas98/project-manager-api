<?php

namespace Database\Seeders\Auth;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate(RoleName::Admin->value);
        Role::findOrCreate(RoleName::ItSupport->value);
    }
}
