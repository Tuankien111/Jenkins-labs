<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class OrderService
 * Handle business logic for Orders
 *
 * @author AI.Architect
 */
class OrderService
{
    /**
     * Create a new order with items
     *
     * @param array $data Order data including items
     * @throws Exception
     * @return Order
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Order Record (Initial total 0)
            $order = Order::create([
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'status'         => 'pending',
                'total_amount'   => 0
            ]);

            $totalAmount = 0;

            // 2. Process Items
            foreach ($data['items'] as $itemData) {
                // Validation logic usually goes here or FormRequest
                $product = Product::findOrFail($itemData['product_id']);
                
                // Simple stock check
                if ($product->stock < $itemData['quantity']) {
                    throw new Exception("Product {$product->name} is out of stock.");
                }

                $price = $product->price;
                $lineTotal = $price * $itemData['quantity'];
                $totalAmount += $lineTotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $itemData['quantity'],
                    'price'      => $price,
                ]);
                
                // Deduct stock (optional but recommended)
                $product->decrement('stock', $itemData['quantity']);
            }

            // 3. Update Order Total
            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items');
        });
    }
}