<?php

namespace Database\Seeders;

use App\Models\ProjectPermission;
use App\Models\ProjectPermissionScheme;
use App\Models\SchemePermission;
use Illuminate\Database\Seeder;

class SchemePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ProjectPermission::all()->keyBy('key');

        $schemes = ProjectPermissionScheme::all()->keyBy('name');

        SchemePermission::truncate();

        //administrator - todos los permisos
        if (isset($schemes['administrator'])) {
            foreach ($permissions as $permission) {
                SchemePermission::create([
                    'permission_scheme_id' => $schemes['administrator']->id,
                    'project_permission_id' => $permission->id,
                ]);
            }
            $this->command->info('administrator: ' . $permissions->count() . 'permissions');
        }

        // leader
        if (isset($schemes['leader'])) {
            $managerPermissions = [
                'view_projects',
                'view_tasks',
                'create_tasks',
                'edit_tasks',
                'delete_tasks',
                'assign_tasks',
                'view_files',
                'upload_files',
                'download_files',
                'delete_files',
                'view_comments',
                'generate_comments',
                'manage_settings',
            ];

            $count = 0;
            foreach ($managerPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['leader']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('leader: ' . $count . 'permissions');
        }

        // developer
        if (isset($schemes['developer'])) {
            $developerPermissions = [
                'view_projects',
                'view_tasks',
                'view_comments',
                'generate_comments',
                'view_files',
                'upload_files',
                'download_files',
                'edit_tasks'
            ];

            $count = 0;
            foreach ($developerPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['developer']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('developer: ' . $count . 'permissions');
        }

        // tester
        if (isset($schemes['tester'])) {
            $testerPermissions = [
                'view_projects',
                'view_tasks',
                'view_files',
                'download_files',
                'view_comments',
                'edit_tasks',
                'generate_comments',
            ];

            $count = 0;
            foreach ($testerPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['tester']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('tester: ' . $count . 'permissions');
        }

        // documente
        if (isset($schemes['documenter'])) {
            $documenterPermissions = [
                'view_projects',
                'view_tasks',
                'view_files',
                'download_files',
                'view_comments',
                'generate_comments',
            ];

            $count = 0;
            foreach ($documenterPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['documenter']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('documenter: ' . $count . 'permissions');
        }
        $this->command->info('====================================');
        $this->command->info('Project permissions assigned to schemes successfully!');

        // Mostrar resumen
        $summary = [];
        foreach ($schemes as $name => $scheme) {
            $count = SchemePermission::where('permission_scheme_id', $scheme->id)->count();
            $summary[] = [$name, $count];
        }

        $this->command->table(
            ['Esquema', 'permissions count'],
            $summary
        );
    }
}
