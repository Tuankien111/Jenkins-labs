<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            'Electronics', 'Computers', 'Smartphones',
            'Men Fashion', 'Women Fashion', 'Home & Kitchen',
            'Beauty & Health', 'Books', 'Sports', 'Toys'
        ];

        /**
         * Không dùng unique() trên randomElement(),
         * mà dùng unique() trên cả factory để tránh lỗi "unique exhausted".
         */
        $name = $this->faker->randomElement($categories);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 99999),
        ];
    }
}
