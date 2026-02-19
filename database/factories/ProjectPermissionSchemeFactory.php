<?php

namespace Database\Factories;

use App\Models\ProjectPermission;
use App\Models\ProjectPermissionScheme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectPermissionSchemeFactory extends Factory
{
    protected $model = ProjectPermissionScheme::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                    'Default Scheme',
                    'Administrator Scheme',
                    'Developer Scheme',
                    'Viewer Scheme',
                    'Contributor Scheme',
                    'Manager Scheme',
                    'Custom Scheme',
                    'Restricted Scheme',
                    'Full Access Scheme',
                    'Read Only Scheme',
                ]) . ' ' . fake()->randomNumber(2),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Configure the model to attach permissions after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProjectPermissionScheme $scheme) {
            // Attach some random permissions if none are attached
            if ($scheme->permissions()->count() === 0) {
                $permissions = ProjectPermission::inRandomOrder()
                    ->take(fake()->numberBetween(3, 10))
                    ->get();

                $scheme->permissions()->attach($permissions);
            }
        });
    }

    /**
     * Set a specific name for the scheme.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Indicate that the scheme should have specific permissions.
     */
    public function withPermissions(array $permissionKeys): static
    {
        return $this->afterCreating(function (ProjectPermissionScheme $scheme) use ($permissionKeys) {
            $permissions = ProjectPermission::whereIn('key', $permissionKeys)->get();
            $scheme->permissions()->sync($permissions);
        });
    }

    /**
     * Attach all existing permissions to the scheme.
     */
    public function withAllPermissions(): static
    {
        return $this->afterCreating(function (ProjectPermissionScheme $scheme) {
            $permissions = ProjectPermission::all();
            $scheme->permissions()->sync($permissions);
        });
    }

    /**
     * Create the default administrator scheme.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrator Scheme',
        ])->afterCreating(function (ProjectPermissionScheme $scheme) {
            $permissions = ProjectPermission::whereIn('key', [
                'view_project',
                'edit_project',
                'delete_project',
                'create_incidence',
                'edit_incidence',
                'delete_incidence',
                'assign_incidence',
                'comment_incidence',
                'view_reports',
                'manage_members',
                'manage_roles',
                'manage_permissions',
                'create_board',
                'edit_board',
                'delete_board',
                'view_team',
                'manage_team',
                'create_attachment',
                'delete_attachment',
                'move_incidence',
                'link_incidence',
                'set_priority',
                'set_state',
                'view_audit_log',
                'export_data',
                'import_data',
                'configure_workflow',
                'manage_categories',
                'manage_versions',
                'manage_components',
                'admin_all',
            ])->get();

            $scheme->permissions()->sync($permissions);
        });
    }

    /**
     * Create the default developer scheme.
     */
    public function developer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Developer Scheme',
        ])->afterCreating(function (ProjectPermissionScheme $scheme) {
            $permissions = ProjectPermission::whereIn('key', [
                'view_project',
                'create_incidence',
                'edit_incidence',
                'delete_incidence',
                'assign_incidence',
                'comment_incidence',
                'view_reports',
                'create_board',
                'edit_board',
                'delete_board',
                'view_team',
                'create_attachment',
                'delete_attachment',
                'move_incidence',
                'link_incidence',
                'set_priority',
                'set_state',
                'export_data',
            ])->get();

            $scheme->permissions()->sync($permissions);
        });
    }

    /**
     * Create the default viewer scheme.
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Viewer Scheme',
        ])->afterCreating(function (ProjectPermissionScheme $scheme) {
            $permissions = ProjectPermission::whereIn('key', [
                'view_project',
                'view_reports',
                'view_team',
                'comment_incidence',
            ])->get();

            $scheme->permissions()->sync($permissions);
        });
    }

    /**
     * Create a scheme with no permissions.
     */
    public function empty(): static
    {
        return $this->afterCreating(function (ProjectPermissionScheme $scheme) {
            $scheme->permissions()->sync([]);
        });
    }

    /**
     * Attach a specific number of random permissions.
     */
    public function withRandomPermissions(int $count = 5): static
    {
        return $this->afterCreating(function (ProjectPermissionScheme $scheme) use ($count) {
            $permissions = ProjectPermission::inRandomOrder()->take($count)->get();
            $scheme->permissions()->sync($permissions);
        });
    }
}
