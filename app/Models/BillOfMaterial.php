<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    protected $fillable = [
        'final_product_id',
        'raw_material_id',
        'quantity',
    ];

    public function finalProduct()
    {
        return $this->belongsTo(Product::class, 'final_product_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(Product::class, 'raw_material_id');
    }
}
