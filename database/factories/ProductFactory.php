<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->unique()->word;
        return [
            'title' => $title,
            'description' => $this->faker->text,
            'category_id' => $this->faker->numberBetween(2, 6),
            'slug' => Str::slug($title),
            'price' => $this->faker->numberBetween(1000, 10000),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'foods', true),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}
