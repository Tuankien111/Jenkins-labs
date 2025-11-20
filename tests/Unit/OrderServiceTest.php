<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    /**
     * Test 1: Đảm bảo tạo đơn hàng thành công và tính tổng tiền chính xác
     */
    public function test_it_can_create_order_and_calculate_total_correctly()
    {
        // Arrange: Tạo 2 sản phẩm
        $category = Category::factory()->create();
        $productA = Product::factory()->create(['category_id' => $category->id, 'price' => 100, 'stock' => 10]);
        $productB = Product::factory()->create(['category_id' => $category->id, 'price' => 50, 'stock' => 10]);

        $data = [
            'customer_name' => 'Test User',
            'customer_phone' => '0909000111',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2], // 100 * 2 = 200
                ['product_id' => $productB->id, 'quantity' => 1], // 50 * 1 = 50
            ]
        ];

        // Act: Gọi Service
        $order = $this->orderService->createOrder($data);

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(250, $order->total_amount); // Tổng phải là 250
        $this->assertEquals('pending', $order->status);
        $this->assertCount(2, $order->items);
    }

    /**
     * Test 2: Đảm bảo kho hàng bị trừ đúng số lượng sau khi mua
     */
    public function test_it_deducts_stock_after_order_creation()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);

        $data = [
            'customer_name' => 'Stock Tester',
            'customer_phone' => '0909000111',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3]
            ]
        ];

        $this->orderService->createOrder($data);

        // Kiểm tra lại stock trong DB
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 7 // 10 - 3 = 7
        ]);
    }

    /**
     * Test 3: Đảm bảo ném ra Exception khi mua quá số lượng tồn kho
     */
    public function test_it_throws_exception_when_product_is_out_of_stock()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);

        $data = [
            'customer_name' => 'Greedy User',
            'customer_phone' => '0909000111',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10] // Mua 10 trong khi chỉ có 5
            ]
        ];

        // Mong đợi Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Product {$product->name} is out of stock.");

        $this->orderService->createOrder($data);
    }
}