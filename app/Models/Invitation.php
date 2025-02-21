<?php

namespace App\Models;

use App\Configs\AppConfig;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'token',
        'used',
    ];

    public function scopeNotExpired($query)
    {
        return $query->where('updated_at', '>', now()->sub(
            AppConfig::EXPIRATION['URL']['value'],
            AppConfig::EXPIRATION['URL']['unit']
        ));
    }

    public function scopePending($query)
    {
        return $query->notUsed()
            ->where('updated_at', '>', now()->sub(
                AppConfig::EXPIRATION['URL']['value'],
                AppConfig::EXPIRATION['URL']['unit']
            ));
    }

    public function scopeExpired($query)
    {
        return $query->where('updated_at', '<=', now()->sub(
            AppConfig::EXPIRATION['URL']['value'],
            AppConfig::EXPIRATION['URL']['unit']
        ));
    }

    public function scopeNotUsed($query)
    {
        return $query->where('used', false);
    }

}
