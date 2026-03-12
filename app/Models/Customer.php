<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function hasCompletedPurchase(): bool
    {
        return $this->sales()->where('status', 'paid')->exists();
    }
}
