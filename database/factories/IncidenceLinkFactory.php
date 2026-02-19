<?php

namespace Database\Factories;

use App\Models\Incidence;
use App\Models\IncidenceLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenceLinkFactory extends Factory
{
    protected $model = IncidenceLink::class;

    public function definition(): array
    {
        return [
            'source_incidence_id' => Incidence::factory(),
            'target_incidence_id' => Incidence::factory(),
            'type' => fake()->randomElement(['relates_to', 'blocks', 'duplicates', 'depends_on']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the link type is "relates_to".
     */
    public function relatesTo(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'relates_to',
        ]);
    }

    /**
     * Indicate that the link type is "blocks".
     */
    public function blocks(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'blocks',
        ]);
    }

    /**
     * Indicate that the link type is "duplicates".
     */
    public function duplicates(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'duplicates',
        ]);
    }

    /**
     * Indicate that the link type is "depends_on".
     */
    public function dependsOn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'depends_on',
        ]);
    }

    /**
     * Set specific source and target incidences.
     */
    public function between(Incidence $source, Incidence $target): static
    {
        return $this->state(fn (array $attributes) => [
            'source_incidence_id' => $source->id,
            'target_incidence_id' => $target->id,
        ]);
    }

    /**
     * Set a specific source incidence.
     */
    public function from(Incidence $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source_incidence_id' => $source->id,
        ]);
    }

    /**
     * Set a specific target incidence.
     */
    public function to(Incidence $target): static
    {
        return $this->state(fn (array $attributes) => [
            'target_incidence_id' => $target->id,
        ]);
    }
}
