<?php

namespace App\Services\Ecommerce;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * TikTok Shop API Service
 * 
 * Simulates integration with TikTok Shop Open API (https://partner.tiktokshop.com/docv2/)
 */
class TiktokShopService implements EcommerceApiInterface
{
    /**
     * @inheritDoc
     */
    public function getOrderList(array $params = []): array
    {
        // Mocking TikTok Shop '/api/orders/search' JSON response
        return [
            'code' => 0,
            'message' => 'success',
            'data' => [
                'more' => false,
                'next_cursor' => '0',
                'orders' => [
                    [
                        'order_id' => '5760' . rand(10000000, 99999999),
                        'order_status' => 'UNPAID', // or AWAITING_SHIPMENT
                        'create_time' => Carbon::now()->timestamp * 1000, // TikTok uses ms
                        'payment_method_name' => 'TikTok Pay',
                        'buyer_uid' => 'xxx-yyy-zzz',
                        'recipient_address' => [
                            'name' => 'TikTok User',
                            'phone' => '+6287' . rand(10000000, 99999999),
                            'full_address' => 'Jl. TikTok Shop User No 1, Bandung'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOrderDetail(string $orderId): array
    {
        // Mocking TikTok Shop '/api/orders/detail/query' format
        return [
            'code' => 0,
            'message' => 'Success',
            'data' => [
                'order_list' => [
                    [
                        'order_id' => $orderId,
                        'order_status' => 'AWAITING_SHIPMENT',
                        'item_list' => [
                            [
                                'sku_id' => '1122334455',
                                'product_id' => '9988776655', // Corresponds to mapping
                                'product_name' => 'TikTok Viral Product',
                                'original_price' => '150000.00',
                                'sale_price' => '120000.00',
                                'quantity' => 1
                            ]
                        ],
                        'payment' => [
                            'total_amount' => '120000.00',
                            'currency' => 'IDR'
                        ]
                    ]
                ]
            ]
        ];
    }
}
