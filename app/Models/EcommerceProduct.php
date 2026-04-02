<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceProduct extends Model
{
    protected $fillable = [
        'ecommerce_integration_id',
        'product_id',
        'platform_product_id',
        'platform_sku_id',
        'platform_product_name',
    ];

    public function integration()
    {
        return $this->belongsTo(EcommerceIntegration::class, 'ecommerce_integration_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
