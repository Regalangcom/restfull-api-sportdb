<?php

namespace Database\Factories;

use App\Models\FavoriteTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FavoriteTeam>
 */
class FavoriteTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => $this->faker->unique()->numerify('######'),
            'team_name' => $this->faker->company(),
            'team_badge' => $this->faker->imageUrl(),
        ];
    }
}
