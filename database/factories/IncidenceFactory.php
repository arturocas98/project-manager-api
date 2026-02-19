<?php

namespace Database\Factories;

use App\Models\Incidence;
use App\Models\IncidencePriority;
use App\Models\IncidenceState;
use App\Models\IncidenceType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenceFactory extends Factory
{
    protected $model = Incidence::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'date' => fake()->dateTimeBetween('-2 months', 'now'),
            'project_id' => Project::factory(),
            'incidence_type_id' => IncidenceType::factory(),
            'incidence_priority_id' => IncidencePriority::factory(),
            'incidence_state_id' => IncidenceState::factory(),
            'created_by_id' => User::factory(),
            'assigned_user_id' => User::factory(),
            'parent_incidence_id' => null,
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    /**
     * Indicate that the incidence is a parent (no parent incidence).
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_incidence_id' => null,
        ]);
    }

    /**
     * Indicate that the incidence is a child of a specific parent.
     */
    public function childOf(Incidence $parentIncidence): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_incidence_id' => $parentIncidence->id,
        ]);
    }

    /**
     * Set a specific user as the creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by_id' => $user->id,
        ]);
    }

    /**
     * Assign to a specific user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_user_id' => $user->id,
        ]);
    }

    /**
     * Set for a specific project.
     */
    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
        ]);
    }

    /**
     * Set a specific type.
     */
    public function ofType(IncidenceType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'incidence_type_id' => $type->id,
        ]);
    }

    /**
     * Set a specific priority.
     */
    public function withPriority(IncidencePriority $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'incidence_priority_id' => $priority->id,
        ]);
    }

    /**
     * Set a specific state.
     */
    public function withState(IncidenceState $state): static
    {
        return $this->state(fn (array $attributes) => [
            'incidence_state_id' => $state->id,
        ]);
    }

    /**
     * Create an incidence with all related models (useful for testing).
     */
    public function withAllRelations(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => Project::factory(),
            'incidence_type_id' => IncidenceType::factory(),
            'incidence_priority_id' => IncidencePriority::factory(),
            'incidence_state_id' => IncidenceState::factory(),
            'created_by_id' => User::factory(),
            'assigned_user_id' => User::factory(),
        ]);
    }
}
