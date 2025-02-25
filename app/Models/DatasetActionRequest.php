<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetActionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dataset_id',
        'type',
        'payload',
        'status'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }
}
