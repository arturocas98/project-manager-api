<?php

namespace Database\Factories;

use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
use App\Models\ProjectRolePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectRolePermissionFactory extends Factory
{
    protected $model = ProjectRolePermission::class;

    public function definition(): array
    {
        return [
            'project_role_id' => ProjectRole::factory(),
            'permission_scheme_id' => ProjectPermissionScheme::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Assign to a specific project role.
     */
    public function forRole(ProjectRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'project_role_id' => $role->id,
        ]);
    }

    /**
     * Assign a specific permission scheme.
     */
    public function withScheme(ProjectPermissionScheme $scheme): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_scheme_id' => $scheme->id,
        ]);
    }

    /**
     * Create an administrator role permission assignment.
     */
    public function administrator(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->administrator()->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a developer role permission assignment.
     */
    public function developer(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->developer()->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a viewer role permission assignment.
     */
    public function viewer(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->viewer()->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a manager role permission assignment.
     */
    public function manager(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->create([
                'name' => 'Manager Scheme',
            ]);

            // Attach all permissions except admin_all
            $permissions = \App\Models\ProjectPermission::where('key', '!=', 'admin_all')->get();
            $scheme->permissions()->sync($permissions);

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a contributor role permission assignment.
     */
    public function contributor(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->withRandomPermissions(8)->create([
                'name' => 'Contributor Scheme',
            ]);

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a reporter role permission assignment.
     */
    public function reporter(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::factory()->create([
                'name' => 'Reporter Scheme',
            ]);

            $permissions = \App\Models\ProjectPermission::whereIn('key', [
                'view_project',
                'create_incidence',
                'comment_incidence',
                'create_attachment',
                'view_reports',
            ])->get();

            $scheme->permissions()->sync($permissions);

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Assign a permission scheme with specific permissions.
     */
    public function withPermissions(array $permissionKeys): static
    {
        return $this->state(function (array $attributes) use ($permissionKeys) {
            $scheme = ProjectPermissionScheme::factory()
                ->withPermissions($permissionKeys)
                ->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Assign a permission scheme with random permissions.
     */
    public function withRandomPermissions(int $count = 5): static
    {
        return $this->state(function (array $attributes) use ($count) {
            $scheme = ProjectPermissionScheme::factory()
                ->withRandomPermissions($count)
                ->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }

    /**
     * Create a permission assignment using existing scheme.
     */
    public function usingExistingScheme(): static
    {
        return $this->state(function (array $attributes) {
            $scheme = ProjectPermissionScheme::inRandomOrder()->first()
                ?? ProjectPermissionScheme::factory()->create();

            return [
                'permission_scheme_id' => $scheme->id,
            ];
        });
    }
}
