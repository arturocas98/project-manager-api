<?php

namespace Database\Seeders;

use App\Models\ProjectPermissionScheme;
use App\Models\ProjectPermission;
use App\Models\SchemePermission;
use Illuminate\Database\Seeder;

class SchemePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los permisos
        $permissions = ProjectPermission::all()->keyBy('key');

        // Obtener todos los esquemas
        $schemes = ProjectPermissionScheme::all()->keyBy('name');

        SchemePermission::truncate();

        // 1. administrators - todos los permisos
        if (isset($schemes['administrators'])) {
            foreach ($permissions as $permission) {
                SchemePermission::create([
                    'permission_scheme_id' => $schemes['administrators']->id,
                    'project_permission_id' => $permission->id,
                ]);
            }
            $this->command->info('administrators: ' . $permissions->count() . 'permissions');
        }

        // 2. project manager
        if (isset($schemes['project manager'])) {
            $managerPermissions = [
                'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
                'manage_members', 'invite_users', 'remove_users',
                'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks', 'assign_tasks',
                'view_files', 'upload_files', 'download_files', 'delete_files',
                'view_reports', 'generate_reports',
                'manage_settings',
            ];

            $count = 0;
            foreach ($managerPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['project manager']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('project manager: ' . $count . 'permissions');
        }

        // 3. team member
        if (isset($schemes['team member'])) {
            $memberPermissions = [
                'view_projects',
                'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks', 'comment_tasks',
                'view_files', 'upload_files', 'download_files',
                'view_reports',
            ];

            $count = 0;
            foreach ($memberPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['team member']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('team member: ' . $count . 'permissions');
        }

        // 4. guest
        if (isset($schemes['guest'])) {
            $guestPermissions = [
                'view_projects',
                'view_tasks', 'comment_tasks',
                'view_files', 'download_files',
                'view_reports',
            ];

            $count = 0;
            foreach ($guestPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['guest']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('guest: ' . $count . 'permissions');
        }

        // 5. supervisor
        if (isset($schemes['supervisor'])) {
            $supervisorPermissions = [
                'view_projects',
                'view_tasks', 'assign_tasks', 'comment_tasks',
                'view_files', 'download_files',
                'view_reports', 'generate_reports',
            ];

            $count = 0;
            foreach ($supervisorPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['supervisor']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('supervisor: ' . $count . 'permissions');
        }

        // 6. external contributor
        if (isset($schemes['external contributor'])) {
            $externalPermissions = [
                'view_projects',
                'view_tasks', 'comment_tasks',
                'view_files', 'download_files', 'upload_files',
            ];

            $count = 0;
            foreach ($externalPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['external contributor']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('external contributor: ' . $count . 'permissions');
        }

        // 7. owner (dueño del proyecto) - mismos permisos que admin
        if (isset($schemes['owner'])) {
            foreach ($permissions as $permission) {
                SchemePermission::create([
                    'permission_scheme_id' => $schemes['owner']->id,
                    'project_permission_id' => $permission->id,
                ]);
            }
            $this->command->info('owner: ' . $permissions->count() . 'permissions');
        }

        // 8. developer
        if (isset($schemes['developer'])) {
            $developerPermissions = [
                'view_projects',
                'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks', 'comment_tasks',
                'view_files', 'upload_files', 'download_files',
                'view_reports',
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

        // 9. tester
        if (isset($schemes['tester'])) {
            $testerPermissions = [
                'view_projects',
                'view_tasks', 'comment_tasks',
                'view_files', 'download_files',
                'view_reports', 'generate_reports',
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

        // 10. client
        if (isset($schemes['client'])) {
            $clientPermissions = [
                'view_projects',
                'view_tasks', 'comment_tasks',
                'view_files', 'download_files',
                'view_reports',
            ];

            $count = 0;
            foreach ($clientPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['client']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('client: ' . $count . 'permissions');
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