<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\EcommerceIntegration;
use App\Services\TikTokShop\TikTokClient;

class TikTokWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Get the signature from header
        $signature = $request->header('x-tts-webhook-sign') ?? $request->header('Authorization');
        
        // Log incoming webhook for debugging
        Log::channel('single')->info('TikTok Webhook Received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        if (!$signature) {
            return response()->json(['code' => 401, 'message' => 'Missing Signature'], 401);
        }

        $appKey = $request->input('app_key') ?? config('services.tiktok.app_key'); // Backup to config
        
        // Try to find integration by app_key, or just take the active one
        $integration = EcommerceIntegration::where('is_active', true)->first();

        if (!$integration) {
            return response()->json(['code' => 404, 'message' => 'Integration not found'], 404);
        }

        // Verify Signature
        $payloadBody = $request->getContent();
        if (!TikTokClient::verifyWebhookSignature($integration->app_secret, $payloadBody, $signature)) {
            Log::warning('TikTok Webhook Invalid Signature');
            return response()->json(['code' => 403, 'message' => 'Invalid Signature'], 403);
        }

        // Process Event
        $type = $request->input('type');
        $data = $request->input('data');

        try {
            switch ($type) {
                case 1: // ORDER_STATUS_CHANGE
                    $this->handleOrderStatusChange($data);
                    break;
                case 3: // REVERSE_ORDER_STATUS_CHANGE (Returns/Cancellations)
                    $this->handleReverseOrder($data);
                    break;
                case 4: // PACKAGE_STATUS_CHANGE
                    $this->handlePackageStatus($data);
                    break;
                case 8: // SETTLEMENT_UPDATE
                    $this->handleSettlement($data);
                    break;
                // Add more cases as needed
            }

            return response()->json(['code' => 0, 'message' => 'success']);
        } catch (\Exception $e) {
            Log::error('TikTok Webhook Processing Error: ' . $e->getMessage());
            return response()->json(['code' => 500, 'message' => 'Internal Error'], 500);
        }
    }

    protected function handleOrderStatusChange($data)
    {
        // Call Order Service to pull updated order
        $orderId = $data['order_id'] ?? null;
        if ($orderId) {
            Log::info("Processing Order Status Change for {$orderId}");
            // app(TikTokOrderService::class)->syncOrder($orderId);
        }
    }

    protected function handleReverseOrder($data)
    {
        // Cancellation or Return
    }

    protected function handlePackageStatus($data)
    {
        // Logistics updates
    }

    protected function handleSettlement($data)
    {
        // Payment settlement updates
    }
}
