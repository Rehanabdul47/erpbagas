<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceSettlement extends Model
{
    protected $fillable = [
        'ecommerce_integration_id',
        'settlement_id',
        'platform_order_id',
        'settlement_amount',
        'platform_fee',
        'currency',
        'settlement_time',
        'cash_transaction_id',
    ];

    protected $casts = [
        'settlement_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'settlement_time' => 'datetime',
    ];

    public function integration()
    {
        return $this->belongsTo(EcommerceIntegration::class, 'ecommerce_integration_id');
    }

    public function cashTransaction()
    {
        return $this->belongsTo(CashTransaction::class);
    }
}
