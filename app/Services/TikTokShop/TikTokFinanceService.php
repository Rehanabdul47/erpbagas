<?php

namespace App\Services\TikTokShop;

use App\Models\EcommerceIntegration;
use App\Models\EcommerceSettlement;
use App\Models\CashTransaction;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TikTokFinanceService
{
    protected $client;
    protected $integration;

    public function __construct(EcommerceIntegration $integration)
    {
        $this->integration = $integration;
        $this->client = new TikTokClient($integration);
    }

    /**
     * Process Settlement Webhook or Sync.
     */
    public function syncSettlement($settlementData)
    {
        DB::beginTransaction();
        try {
            $settlementAmount = $settlementData['settlement_amount'] ?? 0;
            $platformFee = $settlementData['platform_fee'] ?? 0;
            $settlementId = $settlementData['settlement_id'];
            $orderId = $settlementData['order_id'] ?? null;

            // 1. Check if Settlement already exists
            $existing = EcommerceSettlement::where('settlement_id', $settlementId)->first();
            if ($existing) {
                DB::rollBack();
                return; // Already processed
            }

            // 2. We automatically input into CashTransaction (Buku Kas) since user requested it.
            // Find default "Kas Utama / Online Kas" bank account. 
            // Here we assume checking for "Kas" or "Bank" account or letting user map it. For now, we take '111-01' or similar cash account.
            $account = Account::where('account_code', '111.01')->orWhere('name', 'like', '%Kas Utama%')->first();
            
            $cashTransactionId = null;

            if ($account && $settlementAmount > 0) {
                $cashTx = CashTransaction::create([
                    'date' => date('Y-m-d'),
                    'type' => 'in',
                    'amount' => $settlementAmount,
                    'reference' => 'TTS-' . $settlementId,
                    'description' => "Pencairan Saldo TikTok Shop - Order: {$orderId}",
                    'category' => 'Penjualan Online',
                    'account_id' => $account->id,
                    'status' => 'completed',
                ]);
                $cashTransactionId = $cashTx->id;
            }

            // 3. Save Settlement record
            EcommerceSettlement::create([
                'ecommerce_integration_id' => $this->integration->id,
                'settlement_id' => $settlementId,
                'platform_order_id' => $orderId,
                'settlement_amount' => $settlementAmount,
                'platform_fee' => $platformFee,
                'currency' => $settlementData['currency'] ?? 'IDR',
                'settlement_time' => isset($settlementData['settlement_time']) ? date('Y-m-d H:i:s', $settlementData['settlement_time']) : now(),
                'cash_transaction_id' => $cashTransactionId,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("TikTok Settlement Error: " . $e->getMessage());
        }
    }
}
