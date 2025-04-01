<?php

namespace App\Utils;

use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryUtil
{
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
