<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrderItem extends Model
{
    protected $fillable = [
        'ecommerce_order_id',
        'ecommerce_product_id',
        'platform_product_id',
        'platform_sku_id',
        'product_name',
        'sku_name',
        'quantity',
        'original_price',
        'sale_price',
        'platform_discount',
        'seller_discount',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'platform_discount' => 'decimal:2',
        'seller_discount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(EcommerceOrder::class, 'ecommerce_order_id');
    }

    public function ecommerceProduct()
    {
        return $this->belongsTo(EcommerceProduct::class, 'ecommerce_product_id');
    }
}
