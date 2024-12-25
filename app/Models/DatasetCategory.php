<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetCategory extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'category_id'
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
