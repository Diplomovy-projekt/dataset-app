<?php

namespace App\Utils;

use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Image;

class QueryHelper
{
    public static function getDatasetCounts()
    {
        return [
            'total_images' => Image::distinct()->count(),
            'total_annotations' => AnnotationData::distinct()->count(),
            'total_classes' => AnnotationClass::distinct()->count()
        ];
    }
}
