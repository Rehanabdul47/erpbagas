<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type', 'auditable_id', 'action',
        'old_values', 'new_values',
        'user_id', 'ip_address', 'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Model $model, string $action, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): self
    {
        return static::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'description' => $description,
        ]);
    }
}
