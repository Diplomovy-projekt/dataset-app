<?php

namespace App\Models;

use App\Models\Scopes\DatasetVisibilityScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy(DatasetVisibilityScope::class)]
class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'unique_name',
        'description',
        'num_images',
        'total_size',
        'annotation_technique',
        'is_public',
        'is_approved'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(AnnotationClass::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
    public function annotations()
    {
        return $this->hasManyThrough(AnnotationData::class, Image::class);
    }
    public function metadataGroupedByType()
    {
        return $this->metadataValues()
            ->with('metadataType:id,name')
            ->get()
            ->groupBy('metadataType.id')
            ->map(function ($values, $typeId) {
                $typeName = $values->first()->metadataType->name;

                return [
                    'id' => $typeId,
                    'name' => $typeName,
                    'metadataValues' => $values->map(function ($value) {
                        return $value->only(['id', 'value']);
                    })->toArray(),
                ];
            });
    }

    public function metadataValues(): BelongsToMany
    {
        return $this->belongsToMany(MetadataValue::class, 'dataset_metadata');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'dataset_categories');
    }

    public function updateImageCount($difference = null): void
    {
        if ($difference) {
            $this->num_images += $difference;
        } else {
            $this->num_images = $this->images()->count();
        }
        $this->save();
    }

    public function updateSize($size = null): void
    {
        if($size){
            $this->total_size += $size;
        } else {
            $this->total_size = $this->images()->sum('size');
        }
        $this->save();
    }
    public function getStats()
    {
        // Get the number of annotations, images and classes
        $numAnnotations = $this->annotations()->count();
        $numImages = $this->num_images;
        $numClasses = $this->classes()->count();
        return [
            'numAnnotations' => $numAnnotations,
            'numImages' => $numImages,
            'numClasses' => $numClasses,
        ];
    }
}
