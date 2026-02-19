<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardProject;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardProjectFactory extends Factory
{
    protected $model = BoardProject::class;

    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'project_id' => Project::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the pivot belongs to a specific board.
     */
    public function forBoard(Board $board): static
    {
        return $this->state(fn (array $attributes) => [
            'board_id' => $board->id,
        ]);
    }

    /**
     * Indicate that the pivot belongs to a specific project.
     */
    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
        ]);
    }

    /**
     * Indicate that the pivot belongs to specific board and project.
     */
    public function forBoardAndProject(Board $board, Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'board_id' => $board->id,
            'project_id' => $project->id,
        ]);
    }
}
