<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 4: API tạo đơn hàng trả về 201 Created
     */
    public function test_create_order_api_success()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 50]);

        $payload = [
            'customer_name' => 'API User',
            'customer_phone' => '0123456789',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'total_amount', 'status', 'created_at']
                 ]);
    }

    /**
     * Test 5: API trả về lỗi 422 nếu thiếu dữ liệu (Validation)
     */
    public function test_create_order_api_validation_error()
    {
        $payload = [
            'customer_name' => '', // Tên trống
            // Thiếu items
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_name', 'customer_phone', 'items']);
    }

    /**
     * Test 6: API trả về lỗi 400 nếu Logic lỗi (ví dụ: Hết hàng)
     * (Test việc Controller catch Exception từ Service)
     */
    public function test_create_order_api_logic_error()
    {
        $category = Category::factory()->create();
        // Stock = 0
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 0]);

        $payload = [
            'customer_name' => 'Late User',
            'customer_phone' => '0123456789',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);

        // Controller được viết để trả về 400 khi catch Exception
        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    /**
     * Test 7: Lấy danh sách Orders
     */
    public function test_get_orders_list()
    {
        // Giả lập database có sẵn data
        $this->seed(); 

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'customer_name', 'total_amount']
                     ]
                 ]);
    }
}