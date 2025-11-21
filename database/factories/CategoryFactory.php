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

        try {
            // FIX 1: Thay $this->faker bằng fake()
            // FIX 2: Đặt trong try-catch để xử lý khi hết tên trong mảng
            $name = fake()->unique()->randomElement($categories);
        } catch (\Exception $e) {
            // Fallback: Nếu hết tên unique thì random tên mới để không bị crash
            $name = ucfirst(fake()->word()) . ' ' . fake()->numberBetween(1, 1000);
        }

        return [
            'name' => $name,
            // Thêm số ngẫu nhiên vào slug để đảm bảo slug luôn unique dù trùng tên
            'slug' => Str::slug($name) . '-' . fake()->numberBetween(1, 9999),
        ];
    }
}