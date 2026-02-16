<?php

namespace Database\Factories;

use App\Models\ProjectPermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectPermissionFactory extends Factory
{
    protected $model = ProjectPermission::class;

    public function definition(): array
    {
        // Lista de permisos básicos predefinidos
        $permissions = [
            'view_projects' => 'Ver proyectos',
            'create_projects' => 'Crear proyectos',
            'edit_projects' => 'Editar proyectos',
            'delete_projects' => 'Eliminar proyectos',
            'manage_members' => 'Gestionar miembros',
            'view_tasks' => 'Ver tareas',
            'create_tasks' => 'Crear tareas',
            'edit_tasks' => 'Editar tareas',
            'delete_tasks' => 'Eliminar tareas',
            'assign_tasks' => 'Asignar tareas',
            'comment_tasks' => 'Comentar en tareas',
            'view_files' => 'Ver archivos',
            'upload_files' => 'Subir archivos',
            'download_files' => 'Descargar archivos',
            'delete_files' => 'Eliminar archivos',
            'view_reports' => 'Ver reportes',
            'generate_reports' => 'Generar reportes',
            'manage_settings' => 'Gestionar configuración',
            'invite_users' => 'Invitar usuarios',
            'remove_users' => 'Eliminar usuarios',
        ];

        // Seleccionar un permiso aleatorio o usar uno específico
        $key = $this->faker->randomElement(array_keys($permissions));

        return [
            'key' => $key,
            'description' => $permissions[$key],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the permission is for viewing.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'view_projects',
            'description' => 'Ver proyectos',
        ]);
    }

    /**
     * Indicate that the permission is for creating.
     */
    public function createProjects(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'create_projects',
            'description' => 'Crear proyectos',
        ]);
    }

    /**
     * Indicate that the permission is for editing.
     */
    public function edit(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'edit_projects',
            'description' => 'Editar proyectos',
        ]);
    }

    /**
     * Indicate that the permission is for deleting.
     */
    public function delete(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'delete_projects',
            'description' => 'Eliminar proyectos',
        ]);
    }

    /**
     * Create all basic permissions.
     */
    public function allBasicPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'view_projects',
            'description' => 'Ver proyectos',
        ]);
    }

    /**
     * Create task-related permissions.
     */
    public function taskPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $this->faker->randomElement([
                'view_tasks',
                'create_tasks',
                'edit_tasks',
                'delete_tasks',
                'assign_tasks',
            ]),
            'description' => fn (array $attrs) => $this->getDescriptionForKey($attrs['key']),
        ]);
    }

    /**
     * Create file-related permissions.
     */
    public function filePermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $this->faker->randomElement([
                'view_files',
                'upload_files',
                'download_files',
                'delete_files',
            ]),
            'description' => fn (array $attrs) => $this->getDescriptionForKey($attrs['key']),
        ]);
    }

    /**
     * Get description for a specific permission key.
     */
    private function getDescriptionForKey(string $key): string
    {
        $descriptions = [
            // Permisos de proyecto
            'view_projects' => 'Ver proyectos',
            'create_projects' => 'Crear proyectos',
            'edit_projects' => 'Editar proyectos',
            'delete_projects' => 'Eliminar proyectos',
            'manage_members' => 'Gestionar miembros',

            // Permisos de tareas
            'view_tasks' => 'Ver tareas',
            'create_tasks' => 'Crear tareas',
            'edit_tasks' => 'Editar tareas',
            'delete_tasks' => 'Eliminar tareas',
            'assign_tasks' => 'Asignar tareas',
            'comment_tasks' => 'Comentar en tareas',

            // Permisos de archivos
            'view_files' => 'Ver archivos',
            'upload_files' => 'Subir archivos',
            'download_files' => 'Descargar archivos',
            'delete_files' => 'Eliminar archivos',

            // Permisos de reportes
            'view_reports' => 'Ver reportes',
            'generate_reports' => 'Generar reportes',

            // Permisos de administración
            'manage_settings' => 'Gestionar configuración',
            'invite_users' => 'Invitar usuarios',
            'remove_users' => 'Eliminar usuarios',
        ];

        return $descriptions[$key] ?? 'Permiso desconocido';
    }
}
