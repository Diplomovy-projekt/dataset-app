<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'thumbnail_image',
        'annotation_technique',
        'is_public'
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
    public function metadataGroupedByType()
    {
        return $this->metadataValues()
            ->with('metadataType:id,name') // Only load the 'id' and 'name' fields of metadataType
            ->get()
            ->groupBy('metadataType.name') // Group by metadataType name
            ->map(function ($values, $typeName) {
                // Extract the id of the type from the first value in the group
                $typeId = $values->first()->metadataType->id;

                return [
                    'type' => [
                        'id' => $typeId,
                        'name' => $typeName,
                    ],
                    'values' => $values->map(function ($value) {
                        return $value->only(['id', 'value']);
                    }),
                ];
            })
            ->values(); // Reindex the collection to make it an indexed array
    }


    public function metadataValues(): BelongsToMany
    {
        return $this->belongsToMany(MetadataValue::class, 'dataset_metadata');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'dataset_categories');
    }
}
