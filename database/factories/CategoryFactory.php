<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        // Sử dụng danh sách cố định để dữ liệu đẹp và thực tế hơn
        $categories = [
            'Electronics', 'Computers', 'Smartphones', 
            'Men Fashion', 'Women Fashion', 'Home & Kitchen', 
            'Beauty & Health', 'Books', 'Sports', 'Toys'
        ];

        // Lấy random và đảm bảo không trùng lặp (unique)
        $name = fake()->unique()->randomElement($categories);
        
        // Nếu chạy seed > 10 lần sẽ hết tên unique -> fallback về word
        if (!$name) {
             $name = ucfirst($this->faker->word) . ' ' . $this->faker->numberBetween(1, 100);
        }

       return [
            'name' => $name,
            // Thêm số ngẫu nhiên vào slug để đảm bảo slug luôn unique
            'slug' => Str::slug($name) . '-' . fake()->numberBetween(1, 9999),
        ];
    }
}