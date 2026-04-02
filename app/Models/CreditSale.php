<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CreditSale extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'branch_id',
        'sale_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'status',
        'payment_term_days',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // ── Relations ──
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditSaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CreditSalePayment::class);
    }

    // ── Accessors ──
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && Carbon::now()->gt($this->due_date);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue && $this->status !== 'paid') {
            return 'Jatuh Tempo';
        }

        return match ($this->status) {
            'unpaid' => 'Belum Dibayar',
            'partial' => 'Sebagian',
            'paid' => 'Lunas',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue && $this->status !== 'paid') {
            return 'red';
        }

        return match ($this->status) {
            'unpaid' => 'amber',
            'partial' => 'blue',
            'paid' => 'emerald',
            default => 'gray',
        };
    }

    // ── Helpers ──
    public function refreshStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        if ($this->status !== 'paid' && Carbon::now()->gt($this->due_date)) {
            $this->status = 'overdue';
        }

        $this->save();
    }
}
