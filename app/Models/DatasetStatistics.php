<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DatasetStatistics extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'annotation_technique',
        'dataset_count',
        'image_count',
        'annotation_count',
        'class_count',
        'last_updated_at'
    ];

    public static function recalculateAllStatistics()
    {
        $annotationTechniques = Dataset::distinct('annotation_technique')
            ->pluck('annotation_technique')
            ->toArray();

        if (empty($annotationTechniques)) {
            self::query()->update([
                'dataset_count' => 0,
                'image_count' => 0,
                'annotation_count' => 0,
                'class_count' => 0,
                'last_updated_at' => now(),
            ]);

            return;
        }

        foreach ($annotationTechniques as $technique) {
            $stats = self::firstOrCreate(['annotation_technique' => $technique]);

            $datasetCount = Dataset::where('annotation_technique', $technique)->count();

            $imageCount = Image::whereHas('dataset', function ($query) use ($technique) {
                $query->where('annotation_technique', $technique);
            })->count();

            $annotationCount = AnnotationData::whereHas('image.dataset', function ($query) use ($technique) {
                $query->where('annotation_technique', $technique);
            })->count();

            $classCount = AnnotationClass::whereHas('dataset', function ($query) use ($technique) {
                $query->where('annotation_technique', $technique);
            })->distinct()->count('name');

            $stats->dataset_count = $datasetCount;
            $stats->image_count = $imageCount;
            $stats->annotation_count = $annotationCount;
            $stats->class_count = $classCount;
            $stats->last_updated_at = now();
            $stats->save();
        }
    }
}
