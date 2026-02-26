<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'image', 'category_id', 'stock', 'safety_stock',
        'price', 'cogs', 'batch', 'location', 'type'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cogs' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function billOfMaterialsAsFinal(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class, 'final_product_id');
    }

    public function billOfMaterialsAsRaw(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class, 'raw_material_id');
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->stock === 0) return 'out';
        if ($this->stock <= $this->safety_stock) return 'low';
        return 'ok';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'out' => '🔴 Habis',
            'low' => '⚠️ Rendah',
            default => '✅ Aman',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'out' => 'badge-danger',
            'low' => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->price == 0) return 0;
        return round((1 - $this->cogs / $this->price) * 100);
    }
}
