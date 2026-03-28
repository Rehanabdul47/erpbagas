<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'fiscal_period_id', 'date', 'reference', 'description',
        'status', 'source_type', 'source_id',
        'approved_by', 'approved_at', 'cost_center', 'project_code',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ── Relations ──
    public function items()
    {
        return $this->hasMany(JournalItem::class);
    }

    public function fiscalPeriod()
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function auditTrails()
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }

    public function approvalRequests()
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    // ── Scopes ──
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeNotVoid($query)
    {
        return $query->where('status', '!=', 'void');
    }

    // ── Helpers ──
    public function getTotalDebitAttribute(): float
    {
        return $this->items->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return $this->items->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
