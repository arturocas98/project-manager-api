<?php

namespace Database\Factories;

use App\Models\IncidenceState;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenceStateFactory extends Factory
{
    protected $model = IncidenceState::class;

    public function definition(): array
    {
        return [
            'state' => fake()->unique()->randomElement([
                'open',
                'progress',
                'review',
                'testing',
                'resolved',
                'closed',
                'reopened',
                'blocked',
                'on hold'
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'deleted_at' => fake()->optional(0.1)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the state is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'open',
        ]);
    }

    /**
     * Indicate that the state is progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'progress',
        ]);
    }

    /**
     * Indicate that the state is review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'review',
        ]);
    }

    /**
     * Indicate that the state is testing.
     */
    public function testing(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'testing',
        ]);
    }

    /**
     * Indicate that the state is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'resolved',
        ]);
    }

    /**
     * Indicate that the state is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'closed',
        ]);
    }

    /**
     * Indicate that the state is reopened.
     */
    public function reopened(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'reopened',
        ]);
    }

    /**
     * Indicate that the state is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'blocked',
        ]);
    }

    /**
     * Indicate that the state is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'on hold',
        ]);
    }

    /**
     * Create a soft-deleted state.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create a specific state by name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => strtolower($name),
        ]);
    }
}
