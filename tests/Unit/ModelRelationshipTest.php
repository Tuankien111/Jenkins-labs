<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 8: Category có nhiều Products
     */
    public function test_category_has_many_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->products->contains($product));
    }

    /**
     * Test 9: Product thuộc về Category
     */
    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /**
     * Test 10: Order có nhiều OrderItems
     */
    public function test_order_has_many_items()
    {
        $order = Order::create(['customer_name' => 'A', 'customer_phone' => '1']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100
        ]);

        $this->assertTrue($order->items->contains($item));
    }
}