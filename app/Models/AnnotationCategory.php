<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'name',
        'supercategory'
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(AnnotationData::class);
    }
}
