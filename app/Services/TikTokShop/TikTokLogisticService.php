<?php

namespace App\Services\TikTokShop;

use App\Models\EcommerceIntegration;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Log;

class TikTokLogisticService
{
    protected $client;
    protected $integration;

    public function __construct(EcommerceIntegration $integration)
    {
        $this->integration = $integration;
        $this->client = new TikTokClient($integration);
    }

    /**
     * Get Shipping Document (AWB)
     */
    public function getShippingDocument(EcommerceOrder $order)
    {
        try {
            $response = $this->client->post('/api/logistics/shipping_document/get', [
                'order_id' => $order->platform_order_id,
                'document_type' => 'SL', // SL = Shipping Label/AWB
                'document_size' => 'A6'
            ], [
                'shop_id' => $this->integration->shop_id
            ]);

            if (isset($response['data']['doc_url'])) {
                $order->update([
                    'shipping_document_url' => $response['data']['doc_url']
                ]);
                return ['status' => 'success', 'url' => $response['data']['doc_url']];
            } else {
                return ['status' => 'error', 'message' => 'No document URL returned from TikTok.'];
            }
        } catch (\Exception $e) {
            Log::error("TikTok AWB Error ({$order->platform_order_id}): " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Confirm Package Shipment
     * Calls TikTok API to notify that the order is shipped.
     */
    public function confirmShipment(EcommerceOrder $order)
    {
        try {
            // First we need the package ID. Usually fetched from order details or shipping_document API, 
            // but for TikTok Open API v2, the endpoint might differ slightly.
            // Using standard 'rts' (Ready to Ship) sequence.
            $response = $this->client->post('/api/logistics/ship/rts', [ // Verify exact endpoint
                'order_id' => $order->platform_order_id,
            ], [
                'shop_id' => $this->integration->shop_id
            ]);

            if ($response['code'] === 0) {
                // Success
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("TikTok Shipment Confirmation Error: " . $e->getMessage());
            return false;
        }
    }
}
