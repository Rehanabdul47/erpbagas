<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'type', 'description',
        'parent_id', 'level', 'is_header', 'is_control', 'is_active',
        'normal_balance', 'entity_code', 'department_code', 'cost_center_code',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_control' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    // ── Relations ──
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    // ── Scopes ──
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePostable($query)
    {
        return $query->where('is_header', false)->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ── Helpers ──
    public function getFullCodeAttribute(): string
    {
        $segments = array_filter([
            $this->entity_code,
            $this->department_code,
            $this->code,
            $this->cost_center_code,
        ]);
        return implode('-', $segments) ?: $this->code;
    }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense']) || $this->normal_balance === 'debit';
    }
}
