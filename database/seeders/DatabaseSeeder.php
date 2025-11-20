<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Services\OrderService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create 10 Categories
        $categories = Category::factory(10)->create();

        // 2. Create 50 Products (Assign random category from above)
        $products = Product::factory(50)->recycle($categories)->create();

        // 3. Create 5 Orders with random items logic
        // We use the OrderService to ensure total calculation logic is consistent
        $orderService = new OrderService();
        
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 5; $i++) {
            // Random 2 to 5 items per order
            $randomProducts = $products->random(rand(2, 5));
            $items = [];
            
            foreach ($randomProducts as $product) {
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3)
                ];
            }

            $orderData = [
                'customer_name' => $faker->name,
                'customer_phone' => $faker->phoneNumber,
                'items' => $items
            ];

            try {
                $orderService->createOrder($orderData);
            } catch (\Exception $e) {
                // Handle seed error silently
            }
        }
    }
}