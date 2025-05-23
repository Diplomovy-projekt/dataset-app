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

    /**
     * Recalculate statistics for all annotation techniques and overall statistics
     */
    public static function recalculateAllStatistics()
    {
        // Get all existing techniques in the database
        $existingTechniques = self::pluck('annotation_technique')->toArray();

        // Get currently active techniques from approved datasets
        $activeTechniques = Dataset::approved()->distinct('annotation_technique')
            ->pluck('annotation_technique')
            ->toArray();

        // Calculate statistics for active techniques
        foreach ($activeTechniques as $technique) {
            self::recalculateStatisticsForTechnique($technique);
        }

        // For techniques that no longer have datasets, set counts to zero
        $inactiveTechniques = array_diff($existingTechniques, $activeTechniques);
        foreach ($inactiveTechniques as $technique) {
            $stats = self::where('annotation_technique', $technique)->first();
            if ($stats) {
                $stats->dataset_count = 0;
                $stats->image_count = 0;
                $stats->annotation_count = 0;
                $stats->class_count = 0;
                $stats->last_updated_at = now();
                $stats->save();
            }
        }

        // Calculate combined statistics across all techniques
        self::recalculateCombinedStatistics();
    }

    /**
     * Calculate statistics for a specific annotation technique
     *
     * @param string $technique Annotation technique
     */
    private static function recalculateStatisticsForTechnique($technique)
    {
        $stats = self::firstOrCreate(['annotation_technique' => $technique]);

        $datasetCount = Dataset::approved()->where('annotation_technique', $technique)->count();

        $imageCount = Image::whereHas('dataset', function ($query) use ($technique) {
            $query->approved()->where('annotation_technique', $technique);
        })->count();

        $annotationCount = AnnotationData::whereHas('image.dataset', function ($query) use ($technique) {
            $query->approved()->where('annotation_technique', $technique);
        })->count();

        $classCount = AnnotationClass::whereHas('dataset', function ($query) use ($technique) {
            $query->approved()->where('annotation_technique', $technique);
        })->distinct()->count('name');

        $stats->dataset_count = $datasetCount;
        $stats->image_count = $imageCount;
        $stats->annotation_count = $annotationCount;
        $stats->class_count = $classCount;
        $stats->last_updated_at = now();
        $stats->save();
    }

    /**
     * Calculate combined statistics across all annotation techniques
     * This calculates true unique counts across all techniques
     */
    private static function recalculateCombinedStatistics()
    {
        $stats = self::firstOrCreate(['annotation_technique' => 'all']);

        // Count unique approved datasets across all techniques
        $datasetCount = Dataset::approved()->count();

        // Count unique images across all approved datasets
        $imageCount = Image::whereHas('dataset', function ($query) {
            $query->approved();
        })->count();

        // Count all annotations across all approved datasets
        $annotationCount = AnnotationData::whereHas('image.dataset', function ($query) {
            $query->approved();
        })->count();

        // Count unique annotation classes by name across all approved datasets
        $classCount = AnnotationClass::whereHas('dataset', function ($query) {
            $query->approved();
        })->distinct()->count('name');

        $stats->dataset_count = $datasetCount;
        $stats->image_count = $imageCount;
        $stats->annotation_count = $annotationCount;
        $stats->class_count = $classCount;
        $stats->last_updated_at = now();
        $stats->save();
    }
}
