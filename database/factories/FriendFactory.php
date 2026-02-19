<?php

namespace Database\Factories;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FriendFactory extends Factory
{
    protected $model = Friend::class;//'Accepted', 'Rejected', 'Blocked', 'Pending'

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'friend_id' => User::factory(),
            'state' => fake()->randomElement(['pending', 'accepted', 'blocked']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the friend relationship is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'pending',
        ]);
    }

    /**
     * Indicate that the friend relationship is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'accepted',
        ]);
    }

    /**
     * Indicate that the friend relationship is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'blocked',
        ]);
    }

    /**
     * Set a specific user as the owner of the relationship.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set a specific user as the friend.
     */
    public function withFriend(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'friend_id' => $user->id,
        ]);
    }

    /**
     * Create a mutual friendship (both directions).
     */
    public function mutual(User $user1, User $user2, string $state = 'accepted'): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user1->id,
            'friend_id' => $user2->id,
            'state' => $state,
        ]);
    }
}
