<?php

namespace App\ImportService\Strategies;

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

    public function saveImageWithAnnotations($imageData, $datasetId, $classIds): void
    {
        // 3. Save Images and Annotations
        foreach ($imageData as $img) {
            $image = Image::create([
                'dataset_id' => $datasetId,
                'filename' => $img['filename'],
                'width' => $img['width'],
                'height' => $img['height'],
                'size' => $img['size'],
            ]);

            // 4. Save Annotations
            foreach ($img['annotations'] as $annotation) {
                AnnotationData::create([
                    'image_id' => $image->id,
                    'annotation_class_id' => $classIds[$annotation['class_id']], // map to the correct class_id
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
