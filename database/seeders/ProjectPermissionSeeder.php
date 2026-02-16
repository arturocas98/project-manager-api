<?php

namespace Database\Seeders;

use App\Models\ProjectPermission;
use Illuminate\Database\Seeder;

class ProjectPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Proyectos
            'view_projects',
            'create_projects',
            'edit_projects',
            'delete_projects',

            // Miembros
            'manage_members',
            'invite_users',
            'remove_users',

            // Tareas
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'delete_tasks',
            'assign_tasks',
            'comment_tasks',

            // Archivos
            'view_files',
            'upload_files',
            'download_files',
            'delete_files',

            // Reportes
            'view_reports',
            'generate_reports',

            // Configuración
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            ProjectPermission::firstOrCreate(
                ['key' => $permission],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Project permissions seeded successfully!');
    }
}
