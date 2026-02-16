<?php

namespace Database\Factories;

use App\Models\project_permission_scheme;
use App\Models\ProjectPermission;
use Illuminate\Database\Eloquent\Factories\Factory;


class project_permission_schemeFactory extends Factory
{
    protected $model = project_permission_scheme::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Esquemas predefinidos con nombres en español
        $schemes = [
            'Administrador',
            'Gestor de Proyecto',
            'Miembro del Equipo',
            'Invitado',
            'Supervisor',
            'Colaborador Externo',
            'Propietario',
        ];

        return [
            'project_permissions_id' => ProjectPermission::factory(),
            'name' => $this->faker->randomElement($schemes) . ' ' . $this->faker->randomNumber(2),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Esquema de Administrador - tiene todos los permisos
     */
    public function adminScheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrador',
            'project_permissions_id' => function () {
                // Obtener o crear permisos de administrador
                return ProjectPermission::factory()->create([
                    'key' => 'manage_settings',
                    'description' => 'Gestionar configuración'
                ])->id;
            },
        ]);
    }

    /**
     * Esquema de Gestor de Proyecto
     */
    public function projectManagerScheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gestor de Proyecto',
            'project_permissions_id' => function () {
                // Permisos para gestión de proyectos
                $permissionKeys = [
                    'view_projects',
                    'create_projects',
                    'edit_projects',
                    'delete_projects',
                    'manage_members',
                    'invite_users',
                    'remove_users',
                ];

                return ProjectPermission::factory()->create([
                    'key' => $this->faker->randomElement($permissionKeys),
                    'description' => $this->getDescriptionForScheme('manager')
                ])->id;
            },
        ]);
    }

    /**
     * Esquema de Miembro del Equipo
     */
    public function teamMemberScheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Miembro del Equipo',
            'project_permissions_id' => function () {
                // Permisos para tareas y archivos
                $permissionKeys = [
                    'view_projects',
                    'view_tasks',
                    'create_tasks',
                    'edit_tasks',
                    'assign_tasks',
                    'comment_tasks',
                    'view_files',
                    'upload_files',
                    'download_files',
                ];

                return ProjectPermission::factory()->create([
                    'key' => $this->faker->randomElement($permissionKeys),
                    'description' => 'Gestión de tareas y archivos'
                ])->id;
            },
        ]);
    }

    /**
     * Esquema de Invitado
     */
    public function guestScheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Invitado',
            'project_permissions_id' => function () {
                // Solo permisos de vista
                $permissionKeys = [
                    'view_projects',
                    'view_tasks',
                    'view_files',
                    'view_reports',
                    'comment_tasks',
                ];

                return ProjectPermission::factory()->create([
                    'key' => $this->faker->randomElement($permissionKeys),
                    'description' => 'Permisos de solo lectura'
                ])->id;
            },
        ]);
    }

    /**
     * Esquema de Supervisor
     */
    public function supervisorScheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Supervisor',
            'project_permissions_id' => function () {
                // Permisos de supervisión
                $permissionKeys = [
                    'view_projects',
                    'view_tasks',
                    'view_reports',
                    'generate_reports',
                    'assign_tasks',
                    'comment_tasks',
                    'view_files',
                ];

                return ProjectPermission::factory()->create([
                    'key' => $this->faker->randomElement($permissionKeys),
                    'description' => 'Supervisión y reportes'
                ])->id;
            },
        ]);
    }

    /**
     * Esquema con permisos específicos para tareas
     */
    public function withTaskPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Esquema de Tareas - ' . $this->faker->word(),
            'project_permissions_id' => function () {
                return ProjectPermission::factory()->taskPermissions()->create()->id;
            },
        ]);
    }

    /**
     * Esquema con permisos específicos para archivos
     */
    public function withFilePermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Esquema de Archivos - ' . $this->faker->word(),
            'project_permissions_id' => function () {
                return ProjectPermission::factory()->filePermissions()->create()->id;
            },
        ]);
    }

    /**
     * Helper para obtener descripciones según el esquema
     */
    private function getDescriptionForScheme(string $scheme): string
    {
        $descriptions = [
            'admin' => 'Acceso total al sistema',
            'manager' => 'Gestión completa de proyectos',
            'member' => 'Gestión de tareas y archivos',
            'guest' => 'Acceso de solo lectura',
            'supervisor' => 'Supervisión y reportes',
        ];

        return $descriptions[$scheme] ?? 'Permiso de esquema';
    }
}