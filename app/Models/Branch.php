<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'type', 'manager_name', 'address', 'phone', 'is_active', 'status'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENDING = 'sending';
    public const STATUS_RECEIVED = 'received';

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
