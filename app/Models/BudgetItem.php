<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id', 'account_id', 'planned_amount',
        'q1_amount', 'q2_amount', 'q3_amount', 'q4_amount', 'notes',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'q1_amount' => 'decimal:2',
        'q2_amount' => 'decimal:2',
        'q3_amount' => 'decimal:2',
        'q4_amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
