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
        // Obtener todos los permisos
        $permissions = ProjectPermission::all()->keyBy('key');

        // Obtener todos los esquemas
        $schemes = ProjectPermissionScheme::all()->keyBy('name');

        SchemePermission::truncate();

        // 1. Administrators - todos los permisos
        if (isset($schemes['Administrators'])) {
            foreach ($permissions as $permission) {
                SchemePermission::create([
                    'permission_scheme_id' => $schemes['Administrators']->id,
                    'project_permission_id' => $permission->id,
                ]);
            }
            $this->command->info('Administrators: '.$permissions->count().' permisos asignados');
        }

        // 2. Gestor de Proyecto
        if (isset($schemes['Gestor de Proyecto'])) {
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
                        'permission_scheme_id' => $schemes['Gestor de Proyecto']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Gestor de Proyecto: '.$count.' permisos asignados');
        }

        // 3. Miembro del Equipo
        if (isset($schemes['Miembro del Equipo'])) {
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
                        'permission_scheme_id' => $schemes['Miembro del Equipo']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Miembro del Equipo: '.$count.' permisos asignados');
        }

        // 4. Invitado
        if (isset($schemes['Invitado'])) {
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
                        'permission_scheme_id' => $schemes['Invitado']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Invitado: '.$count.' permisos asignados');
        }

        // 5. Supervisor
        if (isset($schemes['Supervisor'])) {
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
                        'permission_scheme_id' => $schemes['Supervisor']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Supervisor: '.$count.' permisos asignados');
        }

        // 6. Colaborador Externo
        if (isset($schemes['Colaborador Externo'])) {
            $externalPermissions = [
                'view_projects',
                'view_tasks', 'comment_tasks',
                'view_files', 'download_files', 'upload_files',
            ];

            $count = 0;
            foreach ($externalPermissions as $key) {
                if (isset($permissions[$key])) {
                    SchemePermission::create([
                        'permission_scheme_id' => $schemes['Colaborador Externo']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Colaborador Externo: '.$count.' permisos asignados');
        }

        // 7. Propietario (dueño del proyecto) - mismos permisos que admin
        if (isset($schemes['Propietario'])) {
            foreach ($permissions as $permission) {
                SchemePermission::create([
                    'permission_scheme_id' => $schemes['Propietario']->id,
                    'project_permission_id' => $permission->id,
                ]);
            }
            $this->command->info('Propietario: '.$permissions->count().' permisos asignados');
        }

        // 8. Desarrollador
        if (isset($schemes['Desarrollador'])) {
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
                        'permission_scheme_id' => $schemes['Desarrollador']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Desarrollador: '.$count.' permisos asignados');
        }

        // 9. Tester
        if (isset($schemes['Tester'])) {
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
                        'permission_scheme_id' => $schemes['Tester']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Tester: '.$count.' permisos asignados');
        }

        // 10. Cliente
        if (isset($schemes['Cliente'])) {
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
                        'permission_scheme_id' => $schemes['Cliente']->id,
                        'project_permission_id' => $permissions[$key]->id,
                    ]);
                    $count++;
                }
            }
            $this->command->info('Cliente: '.$count.' permisos asignados');
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
            ['Esquema', 'Cantidad de Permisos'],
            $summary
        );
    }
}
