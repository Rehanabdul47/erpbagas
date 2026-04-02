<?php

namespace App\Services\TikTokShop;

use App\Models\EcommerceIntegration;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\EcommerceProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TikTokOrderService
{
    protected $client;
    protected $integration;

    public function __construct(EcommerceIntegration $integration)
    {
        $this->integration = $integration;
        $this->client = new TikTokClient($integration);
    }

    /**
     * Sync a specific order from TikTok. Usually triggered via webhook.
     */
    public function syncOrder($orderId)
    {
        try {
            $response = $this->client->post('/api/orders/detail/query', [
                'order_id_list' => [$orderId]
            ], [
                'shop_id' => $this->integration->shop_id
            ]);

            if (isset($response['data']['order_list'][0])) {
                $orderData = $response['data']['order_list'][0];
                $this->processOrder($orderData);
            }
        } catch (\Exception $e) {
            Log::error("TikTok Order Sync Error ($orderId): " . $e->getMessage());
        }
    }

    /**
     * Process order mapping and calculate stock deduction.
     */
    protected function processOrder($data)
    {
        DB::beginTransaction();
        try {
            $order = EcommerceOrder::updateOrCreate(
                [
                    'ecommerce_integration_id' => $this->integration->id,
                    'platform_order_id' => $data['order_id']
                ],
                [
                    'status' => $data['order_status'],
                    'payment_method' => $data['payment_method'] ?? 'UNKNOWN',
                    'total_amount' => $data['payment_info']['total_amount'] ?? 0,
                    'shipping_fee' => $data['payment_info']['shipping_fee'] ?? 0,
                    'platform_discount' => $data['payment_info']['platform_discount'] ?? 0,
                    'seller_discount' => $data['payment_info']['seller_discount'] ?? 0,
                    'subtotal' => $data['payment_info']['sub_total'] ?? 0,
                    'buyer_message' => $data['buyer_message'] ?? null,
                    'recipient_name' => $data['recipient_address']['name'] ?? null,
                    'recipient_phone' => $data['recipient_address']['phone_number'] ?? null,
                    'full_address' => $data['recipient_address']['full_address'] ?? null,
                    'city' => $data['recipient_address']['city'] ?? null,
                    'district' => $data['recipient_address']['district'] ?? null,
                    'province' => $data['recipient_address']['state'] ?? null,
                    'zipcode' => $data['recipient_address']['zipcode'] ?? null,
                    'shipping_provider' => $data['shipping_provider'] ?? null,
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'order_created_at' => isset($data['create_time']) ? date('Y-m-d H:i:s', $data['create_time'] / 1000) : null,
                    'order_paid_at' => isset($data['paid_time']) ? date('Y-m-d H:i:s', $data['paid_time'] / 1000) : null,
                ]
            );

            // Process Items
            if (isset($data['item_list'])) {
                foreach ($data['item_list'] as $itemData) {
                    $ecomProduct = EcommerceProduct::where('ecommerce_integration_id', $this->integration->id)
                        ->where('platform_product_id', $itemData['product_id'])
                        ->where('platform_sku_id', $itemData['sku_id'])
                        ->first();

                    EcommerceOrderItem::updateOrCreate(
                        [
                            'ecommerce_order_id' => $order->id,
                            'platform_product_id' => $itemData['product_id'],
                            'platform_sku_id' => $itemData['sku_id'] ?? null,
                        ],
                        [
                            'ecommerce_product_id' => $ecomProduct ? $ecomProduct->id : null,
                            'product_name' => $itemData['product_name'],
                            'sku_name' => $itemData['sku_name'] ?? null,
                            'quantity' => $itemData['quantity'] ?? 1,
                            'original_price' => $itemData['original_price'] ?? 0,
                            'sale_price' => $itemData['sale_price'] ?? 0,
                            'platform_discount' => $itemData['platform_discount'] ?? 0,
                            'seller_discount' => $itemData['seller_discount'] ?? 0,
                        ]
                    );

                    // Auto-Deduct Stock Validation
                    // If order is paid/awaiting shipment and hasn't been deducted yet
                    $statusDeductible = in_array($order->status, ['AWAITING_SHIPMENT', 'AWAITING_COLLECTION', 'IN_TRANSIT']);
                    
                    if ($statusDeductible && !$order->is_stock_deducted && $ecomProduct && $ecomProduct->product_id) {
                        $localProduct = Product::find($ecomProduct->product_id);
                        if ($localProduct) {
                            $localProduct->decrement('stock_quantity', $itemData['quantity'] ?? 1);
                        }
                    }
                }
                
                // Mark as deducted so we don't deduct again
                $statusDeductible = in_array($order->status, ['AWAITING_SHIPMENT', 'AWAITING_COLLECTION', 'IN_TRANSIT']);
                if ($statusDeductible && !$order->is_stock_deducted) {
                    $order->update(['is_stock_deducted' => true]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("TikTok Replace Order DB Error: " . $e->getMessage());
        }
    }
}
