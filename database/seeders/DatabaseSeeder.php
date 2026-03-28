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
            Auth\LocationSeeder::class,
            Auth\UserSeeder::class,
            Auth\CsvUserSeeder::class,
            ProjectPermissionSeeder::class,
            ProjectPermissionSchemeSeeder::class,
            SchemePermissionSeeder::class,
            IncidenceTypesSeeder::class,
            IncidenceStateSeeder::class,
            IncidencePrioritySeeder::class,
            LinkSeeder::class,
            MenuTableSeeder::class,
            MenuItemTableSeeder::class,
            IncidenceCategorySeeder::class,
            ProjectStateSeeder::class,
        ]);
    }
}
