<?php

namespace Database\Factories;

use App\Models\IncidencePriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidencePriorityFactory extends Factory
{
    protected $model = IncidencePriority::class;

    public function definition(): array
    {
        return [
            'priority' => fake()->unique()->randomElement(['low', 'medium', 'high', 'critical', 'urgent']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the priority is Low.
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    /**
     * Indicate that the priority is Medium.
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'medium',
        ]);
    }

    /**
     * Indicate that the priority is High.
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the priority is Critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
        ]);
    }

    /**
     * Indicate that the priority is Urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Create a specific priority by name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $name,
        ]);
    }
}
