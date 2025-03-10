<?php

namespace App\Utils;

use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\Image;

class QueryUtil
{
    public static function getDatasetCounts(array $datasetIds = null)
    {
        $datasetIds = $datasetIds ?? Dataset::pluck('id');
        return [
            'numDatasets' => Dataset::whereIn('id', $datasetIds)->count(),
            'numImages' => Image::whereIn('dataset_id', $datasetIds)->count(),
            'numAnnotations' => AnnotationData::whereIn('image_id', Image::whereIn('dataset_id', $datasetIds)->pluck('id'))->count(),
            'numClasses' => AnnotationClass::whereIn('dataset_id', $datasetIds)->distinct('name')->count(),
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
