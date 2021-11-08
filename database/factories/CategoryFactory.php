<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->word;
        return [
            'category_name' => $name,
            'category_slug' => $name,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}
