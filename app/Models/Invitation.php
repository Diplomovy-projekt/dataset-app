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

    protected function getExpirationUnit(): string
    {
        return [
            'seconds' => 'seconds',
            'minutes' => 'minutes',
            'hours' => 'hours',
            'days' => 'days',
            'weeks' => 'weeks',
            'months' => 'months',
            'years' => 'years',
        ][AppConfig::URL_EXPIRATION_UNIT] ?? 'hours';
    }

    public function scopeNotExpired($query)
    {
        return $query->where('updated_at', '>', now()->sub(AppConfig::URL_EXPIRATION_VALUE, $this->getExpirationUnit()));
    }

    public function scopePending($query)
    {
        return $query->notUsed()
            ->where('updated_at', '>', now()->sub(AppConfig::URL_EXPIRATION_VALUE, $this->getExpirationUnit()));
    }

    public function scopeExpired($query)
    {
        return $query->where('updated_at', '<=', now()->sub(AppConfig::URL_EXPIRATION_VALUE, $this->getExpirationUnit()));
    }

    public function scopeNotUsed($query)
    {
        return $query->where('used', false);
    }

}
