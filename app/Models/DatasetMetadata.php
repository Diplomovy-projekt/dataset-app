<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DatasetMetadata extends Model
{
    use HasFactory;
    protected $fillable = [
        'dataset_id',
        'metadata_value_id'
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function metadataValue(): BelongsTo
    {
        return $this->belongsTo(MetadataValue::class);
    }

    public static function getGroupedMetadataByCategories(array $selectedCategoryIds): Collection
    {
        return self::query()
            ->whereIn('dataset_id', function ($query) use ($selectedCategoryIds) {
                $query->select('dataset_id')
                    ->from('dataset_categories')
                    ->whereIn('category_id', $selectedCategoryIds);
            })
            ->with(['metadataValue.metadataType']) // Load related metadataType
            ->get()
            ->unique(fn($datasetMetadata) => $datasetMetadata->metadataValue->id)
            ->groupBy(fn($datasetMetadata) => $datasetMetadata->metadataValue->metadataType->name)
            ->map(function ($group, $metadataTypeName) {
                return [
                    'type' => [
                        'name' => $metadataTypeName,
                        'id' => $group->first()->metadataValue->metadataType->id,
                    ],
                    'values' => $group->map(fn($item) => [
                        'id' => $item->metadataValue->id,
                        'value' => $item->metadataValue->value,
                    ]),
                ];
            })
            ->values(); // Reindex the collection
    }
}
