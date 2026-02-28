<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class EcommerceApiService
{
    /**
     * Simulate fetching orders from an external API.
     * In a real scenario, this would use Http::get('https://api.shopee/...')
     * 
     * @return array Array of order data resembling a JSON API response
     * @throws Exception If the mock API "fails"
     */
    public function fetchRecentOrders(): array
    {
        // Simulate network delay (1-2 seconds)
        sleep(rand(1, 2));

        // Simulate occasional API failure (10% chance)
        if (rand(1, 100) <= 10) {
            throw new Exception("Ecommerce API Gateway Timeout. Please try again later.");
        }

        $platforms = ['Shopee', 'Tokopedia', 'TikTok Shop'];
        $couriers = ['J&T Express', 'JNE Reguler', 'Sicepat HALU', 'GoSend', 'GrabExpress'];
        $statuses = ['pending', 'ready_to_ship'];

        $orders = [];
        $orderCount = rand(2, 5); // Fetch 2 to 5 fresh orders

        for ($i = 0; $i < $orderCount; $i++) {
            $shippingCost = rand(10, 50) * 1000;
            $items = $this->generateMockItems();
            
            $itemTotal = collect($items)->sum('subtotal');
            $totalAmount = $itemTotal + $shippingCost;

            $orders[] = [
                'api_transaction_id' => Str::uuid()->toString(), // typical external ID
                'order_sn' => strtoupper(Str::random(3)) . '-' . Carbon::now()->format('ymd') . '-' . rand(1000, 9999),
                'platform' => $platforms[array_rand($platforms)],
                'buyer' => [
                    'name' => 'API Customer ' . rand(100, 999),
                    'phone' => '08' . rand(1000000000, 9999999999),
                ],
                'shipping' => [
                    'address' => 'Jl. E-Commerce Mockup API No.' . rand(1, 999) . ', Jakarta',
                    'courier' => $couriers[array_rand($couriers)],
                    'cost' => $shippingCost,
                    'receipt_number' => null // Usually null for new pending/ready orders
                ],
                'financial' => [
                    'item_total' => $itemTotal,
                    'shipping_fee' => $shippingCost,
                    'total_amount' => $totalAmount,
                ],
                'status' => $statuses[array_rand($statuses)],
                'created_at' => Carbon::now()->subMinutes(rand(1, 120))->toIso8601String(),
                'items' => $items
            ];
        }

        return [
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $orders,
                'total_fetched' => $orderCount
            ]
        ];
    }

    private function generateMockItems(): array
    {
        // We will fetch real products from our DB to associate items properly, 
        // simulating the external API sending SKU/Item identifiers we recognize.
        // For safety, we fetch here, though real APIs pass raw strings/SKUs that we map.
        $products = \App\Models\Product::inRandomOrder()->take(rand(1, 3))->get();
        
        $items = [];
        foreach ($products as $product) {
            $qty = rand(1, 3);
            $price = $product->price;
            $items[] = [
                'external_item_id' => Str::uuid()->toString(),
                'internal_product_id' => $product->id, // Real APIs would use SKU mapped to ID
                'product_name' => $product->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $qty * $price,
            ];
        }

        if (empty($items)) {
            // Fallback generic item if no products exist
            $qty = 1;
            $price = 50000;
            $items[] = [
                'external_item_id' => Str::uuid()->toString(),
                'internal_product_id' => null,
                'product_name' => 'Generic External Item',
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $qty * $price,
            ];
        }

        return $items;
    }
}
