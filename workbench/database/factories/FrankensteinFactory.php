<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Frankenstein;

/**
 * @extends Factory<Frankenstein>
 */
class FrankensteinFactory extends Factory
{
    protected $model = Frankenstein::class;

    public function definition(): array
    {
        return [
            'text' => fake()->word(),
            'number' => fake()->randomNumber(),
            'flag' => fake()->boolean(),
        ];
    }
}
