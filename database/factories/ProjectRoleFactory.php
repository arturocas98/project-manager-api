<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectPermission;
use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
use App\Models\ProjectRolePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectRoleFactory extends Factory
{
    protected $model = ProjectRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'type' => fake()->randomElement(['administrator', 'developer', 'viewer', 'contributor', 'manager', 'reporter']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Configure the model to create related permission scheme after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProjectRole $role) {
            // Create a permission scheme assignment if none exists
            if (!$role->permissionScheme) {
                $scheme = ProjectPermissionScheme::inRandomOrder()->first() ?? ProjectPermissionScheme::factory()->create();

                ProjectRolePermission::factory()->create([
                    'project_role_id' => $role->id,
                    'permission_scheme_id' => $scheme->id,
                ]);
            }
        });
    }

    /**
     * Set a specific type for the role.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the role is administrator.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'administrator',
        ])->afterCreating(function (ProjectRole $role) {
            $scheme = ProjectPermissionScheme::factory()->administrator()->create();

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Indicate that the role is developer.
     */
    public function developer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'developer',
        ])->afterCreating(function (ProjectRole $role) {
            $scheme = ProjectPermissionScheme::factory()->developer()->create();

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Indicate that the role is viewer.
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'viewer',
        ])->afterCreating(function (ProjectRole $role) {
            $scheme = ProjectPermissionScheme::factory()->viewer()->create();

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Indicate that the role is contributor.
     */
    public function contributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'contributor',
        ])->afterCreating(function (ProjectRole $role) {
            // Create contributor scheme with mixed permissions
            $scheme = ProjectPermissionScheme::factory()->withRandomPermissions(8)->create([
                'name' => 'Contributor Scheme',
            ]);

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Indicate that the role is manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manager',
        ])->afterCreating(function (ProjectRole $role) {
            // Manager has most permissions except admin
            $scheme = ProjectPermissionScheme::factory()->create([
                'name' => 'Manager Scheme',
            ]);

            // Attach all permissions except admin_all
            $permissions = ProjectPermission::where('key', '!=', 'admin_all')->get();
            $scheme->permissions()->sync($permissions);

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Indicate that the role is reporter.
     */
    public function reporter(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'reporter',
        ])->afterCreating(function (ProjectRole $role) {
            $scheme = ProjectPermissionScheme::factory()->create([
                'name' => 'Reporter Scheme',
            ]);

            // Reporter can create and comment on incidences
            $permissions = ProjectPermission::whereIn('key', [
                'view_project',
                'create_incidence',
                'comment_incidence',
                'create_attachment',
                'view_reports',
            ])->get();

            $scheme->permissions()->sync($permissions);

            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Assign the role to a specific project.
     */
    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
        ]);
    }

    /**
     * Assign a specific permission scheme to the role.
     */
    public function withPermissionScheme(ProjectPermissionScheme $scheme): static
    {
        return $this->afterCreating(function (ProjectRole $role) use ($scheme) {
            ProjectRolePermission::factory()->create([
                'project_role_id' => $role->id,
                'permission_scheme_id' => $scheme->id,
            ]);
        });
    }

    /**
     * Add users to this role.
     */
    public function withUsers(array $users, int $count = 3): static
    {
        return $this->afterCreating(function (ProjectRole $role) use ($users, $count) {
            if (empty($users)) {
                $users = \App\Models\User::factory($count)->create();
            }

            $role->users()->attach($users);
        });
    }

    /**
     * Create a role without permission scheme.
     */
    public function withoutPermissions(): static
    {
        return $this->afterCreating(function (ProjectRole $role) {
            // Do nothing, leave without permission scheme
        });
    }
}
