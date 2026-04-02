<?php

namespace App\Services\TikTokShop;

use Illuminate\Support\Facades\Http;
use App\Models\EcommerceIntegration;

class TikTokClient
{
    protected $appKey;
    protected $appSecret;
    protected $accessToken;
    protected $shopCipher;
    protected $baseUrl = 'https://open-api.tiktokglobalshop.com';

    public function __construct(EcommerceIntegration $integration)
    {
        $this->appKey = $integration->app_key;
        $this->appSecret = $integration->app_secret;
        $this->accessToken = $integration->access_token;
        $this->shopCipher = $integration->shop_cipher;
    }

    /**
     * Generate the TikTok Shop HMAC-SHA256 signature for API requests.
     */
    protected function generateSignature($path, $params, $body = null)
    {
        // 1. Extract all query parameters excluding 'sign' and 'access_token'
        $keys = array_keys($params);
        $keys = array_filter($keys, function ($k) {
            return $k !== 'sign' && $k !== 'access_token';
        });

        // 2. Sort the keys alphabetically
        sort($keys);

        // 3. Concatenate all params
        $stringToBeSigned = $this->appSecret . $path;
        foreach ($keys as $k) {
            $stringToBeSigned .= $k . $params[$k];
        }

        // 4. Append body if applicable (content-type json)
        if (!empty($body)) {
            $stringToBeSigned .= (is_string($body) ? $body : json_encode($body));
        }

        // 5. Append secret at the end
        $stringToBeSigned .= $this->appSecret;

        // 6. Generate HMAC-SHA256 and convert to hex
        return hash_hmac('sha256', $stringToBeSigned, $this->appSecret);
    }

    /**
     * Build the request URI with standard parameters.
     */
    protected function buildUrl($path, $query = [])
    {
        $query['app_key'] = $this->appKey;
        $query['timestamp'] = time();
        if ($this->shopCipher) {
            $query['shop_cipher'] = $this->shopCipher;
        }

        $signature = $this->generateSignature($path, $query);
        $query['sign'] = $signature;

        return $this->baseUrl . $path . '?' . http_build_query($query);
    }

    /**
     * Perform HTTP GET request.
     */
    public function get($path, $query = [])
    {
        $url = $this->buildUrl($path, $query);
        
        $response = Http::withHeaders([
            'x-tts-access-token' => $this->accessToken,
            'Content-Type' => 'application/json'
        ])->get($url);

        return $response->json();
    }

    /**
     * Perform HTTP POST request.
     */
    public function post($path, $data = [], $query = [])
    {
        $url = $this->buildUrl($path, $query);
        
        $response = Http::withHeaders([
            'x-tts-access-token' => $this->accessToken,
            'Content-Type' => 'application/json'
        ])->post($url, $data);

        return $response->json();
    }

    /**
     * Verify Webhook Signature.
     */
    public static function verifyWebhookSignature($appSecret, $payload, $headerSign)
    {
        // For webhooks, the signature is simple hmac of the payload body with app secret.
        $calculatedSign = hash_hmac('sha256', $payload, $appSecret);
        return hash_equals($calculatedSign, $headerSign);
    }
}
