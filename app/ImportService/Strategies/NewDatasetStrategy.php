<?php

namespace App\ImportService\Strategies;

use App\Configs\AppConfig;
use App\ImageService\ImageProcessor;
use App\ImportService\Interfaces\DatasetSavingStrategyInterface;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Utils\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewDatasetStrategy implements DatasetSavingStrategyInterface
{
    public function saveToDatabase($mappedData, $requestData): Response
    {
        try {
            $classes = $mappedData['classes'];
            $imageData = $mappedData['images'];
            // 1. Create Dataset
            $requestDataset = Dataset::create([
                'user_id' => auth()->id() ?? "1",
                'display_name' => $requestData['display_name'],
                'unique_name' => $requestData['unique_name'],
                'description' => $requestData['description'] ?? "",
                'num_images' => count($imageData),
                'total_size' => array_sum(array_column($imageData, 'size')),
                'annotation_technique' => $requestData['technique'],
                'is_public' => false,
            ]);
            // 2. Save Classes
            $classIds = [];
            $classesToSample = [];
            foreach ($classes['names'] as $categoryName) {
                $category = AnnotationClass::updateOrCreate([
                    'dataset_id' => $requestDataset->id,
                    'name' => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);
                $classIds[] = $category->id;
                if ($category->wasRecentlyCreated) {
                    $classesToSample[] = $category->id;
                }
            }

            // 3. Save Images and Annotations
            foreach ($imageData as $img) {
                $image = Image::create([
                    'dataset_id' => $requestDataset->id,
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

            // 5. Save dataset metadata
            foreach ($requestData['metadata'] as $id => $value) {
                DatasetMetadata::create([
                    'dataset_id' => $requestDataset->id,
                    'metadata_value_id' => $id,
                ]);
            }

            // 6. Save dataset categories
            foreach ($requestData['categories'] as $id) {
                DatasetCategory::create([
                    'dataset_id' => $requestDataset->id,
                    'category_id' => $id,
                ]);
            }

            return Response::success(data: ['classesToSample' => $classesToSample]);
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

    public function handleRollback(array $requestData): void
    {
        // Rollback the dataset upload
        if(Storage::disk('datasets')->exists($requestData['unique_name'])){
            Storage::disk('datasets')->deleteDirectory($requestData['unique_name']);
        }
        DB::rollBack();
    }
}
