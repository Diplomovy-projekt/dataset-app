<?php

namespace App\ImportService\Strategies;

use App\Configs\AppConfig;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Image;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;

abstract class BaseStrategy
{
    public function assignColorsToClasses($classIds): void
    {
        $coloredClasses = Util::generateDistinctColors($classIds);

        $updateData = [];
        foreach ($coloredClasses as $id => $color) {
            $updateData[] = "WHEN `id` = '$id' THEN '$color'";
        }

        $ids = implode(", ", array_keys($coloredClasses));
        $updates = implode(" ", $updateData);

        DB::statement("
            UPDATE annotation_classes
            SET rgb = CASE $updates END
            WHERE id IN ($ids)
        ");
    }

    public function saveImageWithAnnotations($imageData, $dataset, $classIds): void
    {
        // 1. Save Images and Annotations
        foreach ($imageData as $img) {
            $image = Image::create([
                'dataset_id' => $dataset->id,
                'dataset_folder' => $dataset->unique_name,
                'path' => AppConfig::DATASETS_PATH['public'] . $dataset->unique_name . '/' . AppConfig::FULL_IMG_FOLDER . $img['filename'],
                'filename' => $img['filename'],
                'width' => $img['width'],
                'height' => $img['height'],
                'size' => $img['size'],
            ]);

            // 2. Save Annotations
            foreach ($img['annotations'] as $annotation) {
                AnnotationData::create([
                    'image_id' => $image->id,
                    'annotation_class_id' => $classIds[$annotation['class_id']],
                    'x' => $annotation['x'],
                    'y' => $annotation['y'],
                    'width' => $annotation['width'],
                    'height' => $annotation['height'],
                    'segmentation' => $annotation['segmentation'],
                ]);
            }
        }
    }
}
