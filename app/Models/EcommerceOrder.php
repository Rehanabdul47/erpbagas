<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'platform',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'shipping_courier',
        'receipt_number',
        'shipping_cost',
        'total_amount',
        'subtotal_product',
        'shipping_fee_buyer_paid',
        'shipping_fee_discount',
        'voucher_platform',
        'voucher_seller',
        'admin_fee',
        'platform_fee',
        'fixed_fee',
        'service_fee',
        'processing_fee',
        'total_income',
        'status',
        'notes',
        'order_date'
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(EcommerceOrderItem::class);
    }
}
