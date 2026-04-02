<?php

namespace App\Services\TikTokShop;

use App\Models\EcommerceIntegration;
use App\Models\EcommerceProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class TikTokProductService
{
    protected $client;
    protected $integration;

    public function __construct(EcommerceIntegration $integration)
    {
        $this->integration = $integration;
        $this->client = new TikTokClient($integration);
    }

    /**
     * Fetch all products from TikTok Shop and store them locally for mapping.
     */
    public function syncProducts()
    {
        try {
            $response = $this->client->post('/api/products/search', [
                'page_size' => 100,
            ], [
                'shop_id' => $this->integration->shop_id
            ]);

            if (isset($response['data']['products'])) {
                foreach ($response['data']['products'] as $item) {
                    $tiktokProductId = $item['product_id'];
                    $productName = $item['product_name'];
                    
                    // A product might have multiple SKUs
                    if (isset($item['skus']) && count($item['skus']) > 0) {
                        foreach ($item['skus'] as $sku) {
                            $this->updateOrCreateProductMapping(
                                $tiktokProductId, 
                                $sku['id'], 
                                $productName . ' - ' . ($sku['seller_sku'] ?? 'No SKU')
                            );
                        }
                    } else {
                        $this->updateOrCreateProductMapping($tiktokProductId, null, $productName);
                    }
                }
            }

            return ['status' => 'success', 'message' => 'Products synced successfully'];
        } catch (\Exception $e) {
            Log::error('TikTok Product Sync Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function updateOrCreateProductMapping($productId, $skuId, $name)
    {
        EcommerceProduct::updateOrCreate(
            [
                'ecommerce_integration_id' => $this->integration->id,
                'platform_product_id' => $productId,
                'platform_sku_id' => $skuId,
            ],
            [
                'platform_product_name' => $name,
            ]
        );
    }
}
