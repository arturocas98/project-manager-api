<?php

namespace Database\Factories;

use App\Models\ProjectRole;
use App\Models\ProjectUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectUserFactory extends Factory
{
    protected $model = ProjectUser::class;

    public function definition(): array
    {
        return [
            'project_role_id' => ProjectRole::factory(),
            'user_id' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'deleted_at' => fake()->optional(0.1)->dateTimeBetween('-6 months', '-1 day'),
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
     * Assign to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Assign a user to a project with a specific role type.
     */
    public function withRoleType(string $roleType): static
    {
        return $this->state(function (array $attributes) use ($roleType) {
            // Try to find existing role of that type in the project
            $role = null;

            if (isset($attributes['project_role_id'])) {
                $role = ProjectRole::find($attributes['project_role_id']);
            }

            if (!$role) {
                $role = ProjectRole::factory()->create([
                    'type' => $roleType,
                ]);
            }

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user an administrator in the project.
     */
    public function asAdministrator(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->administrator()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user a developer in the project.
     */
    public function asDeveloper(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->developer()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user a viewer in the project.
     */
    public function asViewer(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->viewer()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user a manager in the project.
     */
    public function asManager(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->manager()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user a contributor in the project.
     */
    public function asContributor(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->contributor()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Make the user a reporter in the project.
     */
    public function asReporter(): static
    {
        return $this->state(function (array $attributes) {
            $role = ProjectRole::factory()->reporter()->create();

            return [
                'project_role_id' => $role->id,
            ];
        });
    }

    /**
     * Indicate that the assignment is active (not deleted).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => null,
        ]);
    }

    /**
     * Indicate that the assignment is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Assign multiple users to the same role.
     */
    public function forMultipleUsers(array $users): static
    {
        return $this->afterCreating(function (ProjectUser $projectUser) use ($users) {
            foreach ($users as $user) {
                if ($user->id !== $projectUser->user_id) {
                    ProjectUser::factory()->create([
                        'project_role_id' => $projectUser->project_role_id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        });
    }
}
