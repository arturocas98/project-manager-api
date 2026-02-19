<?php

namespace Database\Factories;

use App\Models\IncidenceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenceTypeFactory extends Factory
{
    protected $model = IncidenceType::class;

    public function definition(): array
    {
        return [
            'type' => fake()->unique()->randomElement([
                'epic',
                'history_user',
                'task',
                'bug',
                'subtask'
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'deleted_at' => fake()->optional(0.1)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the type is epic.
     */
    public function epic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'epic',
        ]);
    }

    /**
     * Indicate that the type is history_user.
     */
    public function historyUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'history_user',
        ]);
    }

    /**
     * Indicate that the type is task.
     */
    public function task(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'task',
        ]);
    }

    /**
     * Indicate that the type is bug.
     */
    public function bug(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bug',
        ]);
    }

    /**
     * Indicate that the type is subtask.
     */
    public function subtask(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'subtask',
        ]);
    }

    /**
     * Create a soft-deleted type.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create a specific type by name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => strtolower($name),
        ]);
    }

}
