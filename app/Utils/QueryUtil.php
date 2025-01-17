<?php

namespace App\Utils;

use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\Image;

class QueryUtil
{
    public static function getDatasetCounts()
    {
        return [
            'total_images' => Image::distinct()->count(),
            'total_annotations' => AnnotationData::distinct()->count(),
            'total_classes' => AnnotationClass::distinct()->count()
        ];
    }

    public static function getFirstImage(string $datasetUniqueName)
    {
        $image = Dataset::where('unique_name', $datasetUniqueName)
            ->with(['images' => function($query) {
                $query->limit(1);
            }, 'images.annotations.class'])
            ->first()
            ->images
            ->first();
        return $image;
    }
}
