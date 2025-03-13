<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dataset_id',
        'type',
        'payload',
        'status',
        'comment',
        'reviewed_by',
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
