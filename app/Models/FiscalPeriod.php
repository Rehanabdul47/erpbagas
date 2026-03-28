<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiscalPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'start_date', 'end_date', 'status',
        'closed_by', 'closed_at', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public static function findForDate($date): ?self
    {
        return static::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
}
