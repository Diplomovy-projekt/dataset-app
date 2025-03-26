<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DatasetCategory extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetCategoryFactory> */
    use HasFactory;

    public $timestamps = false;

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

    public static function getAllUniqueCategories(): Collection
    {
        return Category::whereIn('id', self::select('category_id')->distinct())
            ->get(['id', 'name']);
    }
}
