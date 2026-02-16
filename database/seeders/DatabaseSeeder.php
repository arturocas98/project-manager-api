<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            Auth\RoleSeeder::class,
            Auth\UserSeeder::class,
            ProjectPermissionSeeder::class,
            ProjectPermissionSchemeSeeder::class,
            SchemePermissionSeeder::class,
        ]);
    }
}
