<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceIntegration extends Model
{
    protected $fillable = [
        'platform_name',
        'app_key',
        'app_secret',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'shop_id',
        'shop_cipher',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(EcommerceProduct::class);
    }

    public function orders()
    {
        return $this->hasMany(EcommerceOrder::class);
    }
}
