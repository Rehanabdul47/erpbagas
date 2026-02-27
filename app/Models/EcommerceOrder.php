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
        'status',
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
