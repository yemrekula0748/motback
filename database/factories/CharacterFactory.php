<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
{
    protected $model = Character::class;

    public function definition(): array
    {
        $class = $this->faker->randomElement(['savasco', 'okcu', 'saman']);
        $stats = Character::getStartingStats($class);

        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->unique()->lexify('Hero????'),
            'class'       => $class,
            'level'       => 1,
            'experience'  => 0,
            'current_map' => 'ThirdPersonMap',
            'pos_x'       => 0.0,
            'pos_y'       => 0.0,
            'pos_z'       => 0.0,
            'gold'        => 100,
            'silver'      => 0,
            ...$stats,
        ];
    }
}
