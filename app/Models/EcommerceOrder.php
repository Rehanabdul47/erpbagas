<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    protected $fillable = [
        'ecommerce_integration_id',
        'platform_order_id',
        'status',
        'payment_method',
        'total_amount',
        'shipping_fee',
        'platform_discount',
        'seller_discount',
        'subtotal',
        'buyer_message',
        'recipient_name',
        'recipient_phone',
        'full_address',
        'city',
        'district',
        'province',
        'zipcode',
        'shipping_provider',
        'tracking_number',
        'shipping_document_url',
        'is_stock_deducted',
        'order_created_at',
        'order_paid_at',
    ];

    protected $casts = [
        'is_stock_deducted' => 'boolean',
        'order_created_at' => 'datetime',
        'order_paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'platform_discount' => 'decimal:2',
        'seller_discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function integration()
    {
        return $this->belongsTo(EcommerceIntegration::class, 'ecommerce_integration_id');
    }

    public function items()
    {
        return $this->hasMany(EcommerceOrderItem::class);
    }
}
