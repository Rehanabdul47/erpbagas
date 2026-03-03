<?php

namespace App\Services\Ecommerce;

use App\Models\EcommerceOrder;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Tokopedia API Service
 * 
 * Simulates integration with Tokopedia OpenAPI (https://developer.tokopedia.com/openapi/v1)
 */
class TokopediaService implements EcommerceApiInterface
{
    /**
     * @inheritDoc
     */
    public function getOrderList(array $params = []): array
    {
        // Mocking a Tokopedia '/v2/order/list' JSON response format
        return [
            'header' => [
                'process_time' => Carbon::now()->timestamp,
                'messages' => 'success',
                'reason' => 'your request has been processed successfully',
                'error_code' => 0
            ],
            'data' => [
                'fs_id' => 'some_tokopedia_fs_id',
                'shop_id' => '123456',
                'orders' => [
                    [
                        'order_id' => 'INV/' . date('Ymd') . '/MPL/' . rand(100000, 999999),
                        'order_status' => 220, // 220 is Payment Verified in TP
                        'order_status_name' => 'Payment Verified',
                        'buyer_info' => [
                            'buyer_id' => 88888,
                            'buyer_fullname' => 'Tokopedia Shopper',
                            'buyer_email' => 'buyer@tokopedia.com',
                            'buyer_phone' => '0812' . rand(1000000, 9999999)
                        ],
                        'shipping_info' => [
                            'courier_name' => 'JNE',
                            'awb' => 'JNE' . time(),
                            'destination' => 'Jakarta Selatan'
                        ],
                        'item_price' => rand(50000, 250000),
                        'shipping_price' => rand(15000, 30000),
                        'create_time' => Carbon::now()->timestamp
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
        // Mocking a Tokopedia '/v2/order/info' response 
        return [
            'data' => [
                'order_id' => $orderId,
                'shop_id' => '123456',
                'order_status' => 400,
                'buyer_info' => [
                    'buyer_fullname' => 'Tokopedia Detailed Shopper'
                ],
                'order_info' => [
                    'order_detail' => [
                        [
                            'order_detail_id' => rand(1000000, 9999999),
                            'product_id' => rand(100, 999), // Corresponds to internal mapping
                            'product_name' => 'Mock Product A',
                            'quantity' => 2,
                            'product_price' => 50000,
                            'subtotal_price' => 100000
                        ]
                    ]
                ],
                'payment_info' => [
                    'gateway_name' => 'GoPay',
                    'payment_amount' => 100000
                ]
            ]
        ];
    }
}
