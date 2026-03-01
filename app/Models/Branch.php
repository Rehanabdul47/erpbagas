<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'type', 'manager_name', 'address', 'phone', 'is_active'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
