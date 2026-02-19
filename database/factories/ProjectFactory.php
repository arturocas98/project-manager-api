<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'key' => strtoupper(substr(str_replace(' ', '', $name), 0, 4)) . fake()->randomNumber(3),
            'description' => fake()->optional(0.8)->paragraphs(3, true),
            'created_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'updated_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'deleted_at' => fake()->optional(0.05)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the project is active (not deleted).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => null,
        ]);
    }

    /**
     * Indicate that the project is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Set a specific user as the creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Set a specific project key.
     */
    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => strtoupper($key),
        ]);
    }

    /**
     * Create a project with a short name (useful for keys).
     */
    public function shortName(): static
    {
        $name = fake()->unique()->lexify('???');

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'key' => strtoupper($name),
        ]);
    }

    /**
     * Create a project without description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}
