<?php

namespace Database\Factories;

use App\Models\ProjectPermission;
use App\Models\ProjectPermissionScheme;
use App\Models\SchemePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchemePermissionFactory extends Factory
{
    protected $model = SchemePermission::class;

    public function definition(): array
    {
        return [
            'permission_scheme_id' => ProjectPermissionScheme::factory(),
            'project_permission_id' => ProjectPermission::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Assign to a specific permission scheme.
     */
    public function forScheme(ProjectPermissionScheme $scheme): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_scheme_id' => $scheme->id,
        ]);
    }

    /**
     * Assign a specific permission.
     */
    public function withPermission(ProjectPermission $permission): static
    {
        return $this->state(fn (array $attributes) => [
            'project_permission_id' => $permission->id,
        ]);
    }

    /**
     * Assign a permission by its key.
     */
    public function withPermissionKey(string $permissionKey): static
    {
        return $this->state(function (array $attributes) use ($permissionKey) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => $permissionKey],
                ['key' => $permissionKey]
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign multiple permissions to a scheme.
     */
    public function withMultiplePermissions(array $permissionKeys): static
    {
        return $this->afterCreating(function (SchemePermission $schemePermission) use ($permissionKeys) {
            foreach ($permissionKeys as $key) {
                if ($key !== $schemePermission->permission->key) {
                    $permission = ProjectPermission::firstOrCreate(['key' => $key]);

                    SchemePermission::factory()->create([
                        'permission_scheme_id' => $schemePermission->permission_scheme_id,
                        'project_permission_id' => $permission->id,
                    ]);
                }
            }
        });
    }

    /**
     * Assign view permissions.
     */
    public function viewPermissions(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => 'view_project'],
                ['key' => 'view_project']
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign edit permissions.
     */
    public function editPermissions(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => 'edit_project'],
                ['key' => 'edit_project']
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign create incidence permission.
     */
    public function createIncidence(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => 'create_incidence'],
                ['key' => 'create_incidence']
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign delete incidence permission.
     */
    public function deleteIncidence(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => 'delete_incidence'],
                ['key' => 'delete_incidence']
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign manage members permission.
     */
    public function manageMembers(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::firstOrCreate(
                ['key' => 'manage_members'],
                ['key' => 'manage_members']
            );

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }

    /**
     * Assign a random permission.
     */
    public function randomPermission(): static
    {
        return $this->state(function (array $attributes) {
            $permission = ProjectPermission::inRandomOrder()->first()
                ?? ProjectPermission::factory()->create();

            return [
                'project_permission_id' => $permission->id,
            ];
        });
    }
}
