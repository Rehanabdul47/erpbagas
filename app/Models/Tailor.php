<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tailor extends Model
{
    protected $fillable = ['name', 'phone', 'address'];

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
