<?php

namespace App\ImportService\Strategies;

use App\Configs\AppConfig;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Image;
use App\Utils\Response;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function saveClasses($classes, $dataset)
    {
        $classIds = [];
        $classesToSample = [];
        $duplicateClassMap = [];

        foreach ($classes as $className) {
            if (!isset($duplicateClassMap[$className])) {
                $class = AnnotationClass::firstOrCreate([
                    'dataset_id'   => $dataset->id,
                    'name'         => $className,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);

                $duplicateClassMap[$className] = $class->id;
            }

            // Used to store all class ids accounting for duplicates needed for image annotations
            $classIds[] = $duplicateClassMap[$className];

            $filesCount = count(Storage::disk('datasets')->files(
                "{$dataset->unique_name}/" . AppConfig::CLASS_IMG_FOLDER . "/{$duplicateClassMap[$className]}"
            ));
            $shouldSample = $class->wasRecentlyCreated || $filesCount < AppConfig::SAMPLES_COUNT;
            if ($shouldSample && !in_array($duplicateClassMap[$className], $classesToSample)) {
                $classesToSample[] = $duplicateClassMap[$className];
            }
        }
        return [$classIds, $classesToSample];

    }

    public function saveImageWithAnnotations($imageData, $dataset, $classIds): void
    {
        // 1. Save Images and Annotations
        foreach ($imageData as $img) {
            $image = Image::create([
                'dataset_id' => $dataset->id,
                'dataset_folder' => $dataset->unique_name,
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
                    'segmentation' => $annotation['segmentation'] ?? null,
                ]);
            }
        }
    }

    public function addUniqueSuffixes($datasetFolder, &$mappedData): Response
    {
        $images = &$mappedData['images'];
        $datasetPath = AppConfig::DEFAULT_DATASET_LOCATION . $datasetFolder . '/' . AppConfig::FULL_IMG_FOLDER;

        try {
            foreach ($images as &$image) {
                $suffix = uniqid('_da_');
                $newName = pathinfo($image['filename'], PATHINFO_FILENAME) . $suffix . '.' . pathinfo($image['filename'], PATHINFO_EXTENSION);

                $oldPath = $datasetPath . '/' . $image['filename'];
                $newPath = $datasetPath . '/' . $newName;

                if (Storage::move($oldPath, $newPath)) {
                    $image['filename'] = $newName;
                }
            }

            return Response::success();
        } catch (\Exception $e) {
            return Response::error("Failed to add unique suffixes to images", $e->getMessage());
        }
    }




}
