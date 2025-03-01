<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 1, 100),
            'old_price' => fake()->randomFloat(2, 1, 100),
            'count' => fake()->randomNumber(2),
            'category_id' => Category::factory()
        ];
    }
}
