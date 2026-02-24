<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    protected $fillable = [
        'code',
        'type', // percentage, fixed
        'value',
        'max_uses',
        'max_uses_per_customer',
        'used_count',
        'valid_from',
        'valid_until',
        'description',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function isValid(?int $customerId = null)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($customerId && $this->max_uses_per_customer > 0) {
            $userUsage = Sale::where('customer_id', $customerId)
                ->where('redeem_code_id', $this->id)
                ->count();
            
            if ($userUsage >= $this->max_uses_per_customer) {
                return false;
            }
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
