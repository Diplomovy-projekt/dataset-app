<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationClass extends Model
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

    public function annotationsForClass(int $datasetId): int
    {
        return $this->annotations()
            ->whereHas('class', function ($query) use ($datasetId) {
                $query->where('dataset_id', $datasetId);
            })
            ->count();
    }

    public function imagesForClass(int $datasetId): int
    {
        return \App\Models\Image::whereHas('annotations', function ($query) use ($datasetId) {
            $query->whereHas('class', function ($classQuery) use ($datasetId) {
                $classQuery->where('id', $this->id)
                    ->where('dataset_id', $datasetId);
            });
        })->distinct('id')->count();
    }
}
