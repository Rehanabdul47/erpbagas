<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    protected $fillable = [
        'branch_id',
        'code',
        'type', // percentage, fixed
        'value',
        'max_uses',
        'max_uses_per_customer',
        'only_new_customer',
        'only_returning_customer',
        'used_count',
        'valid_from',
        'valid_until',
        'description',
        'is_active',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'only_new_customer' => 'boolean',
        'only_returning_customer' => 'boolean',
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

        if ($this->valid_until && $now->gt($this->valid_until->endOfDay())) {
            return false;
        }

        // New vs Returning logic
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $hasPurchased = $customer->hasCompletedPurchase();
                
                if ($this->only_new_customer && $hasPurchased) {
                    return false; // Customer is NOT a new customer
                }
                
                if ($this->only_returning_customer && !$hasPurchased) {
                    return false; // Customer is NOT a returning customer
                }
            }
        }

        return true;
    }
}
