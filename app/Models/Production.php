<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'date',
        'user_id',
        'tailor_id',
        'status',
        'stage',
        'notes',
    ];

    public function getStagesAttribute()
    {
        // Default stages if none are defined on the category
        $defaultStages = ['Proses Pembuatan'];
        
        if ($this->product && $this->product->category && $this->product->category->production_stages) {
            $stages = $this->product->category->production_stages;
            return is_array($stages) && count($stages) > 0 ? $stages : $defaultStages;
        }

        return $defaultStages;
    }

    public function getNextStage()
    {
        if ($this->status !== 'processing') {
            return null;
        }

        $stages = $this->stages;
        $currentIndex = array_search($this->stage, $stages);
        
        if ($currentIndex === false) {
            // If current stage is not in the list (e.g., category stages changed), just return the first one or null
            return $stages[0] ?? null;
        }

        if ($currentIndex < count($stages) - 1) {
            return $stages[$currentIndex + 1];
        }

        return 'completed'; // Indicates production finished after last stage
    }

    public function getStageLabelAttribute()
    {
        return $this->stage; // Since stages are dynamic strings now, the label is the stage name itself
    }

    public function isLastStageAttribute()
    {
        $stages = $this->stages;
        return $this->stage === end($stages);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }
}
