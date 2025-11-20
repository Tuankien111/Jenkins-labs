<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        // SỬA LỖI: Thay thế 'productName' bằng cách ghép 3 từ ngẫu nhiên
        // Ví dụ kết quả: "Awesome Concrete Shirt", "Small Soft Computer"
        $name = ucwords($this->faker->words(3, true)); 

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            // Thêm random string vào slug để đảm bảo unique tuyệt đối khi seed số lượng lớn
            'slug' => Str::slug($name . '-' . Str::random(5)),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(10, 100),
            'description' => $this->faker->sentence(10),
        ];
    }
}