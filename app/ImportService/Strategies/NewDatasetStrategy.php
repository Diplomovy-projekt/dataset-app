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

class NewDatasetStrategy extends BaseStrategy implements DatasetSavingStrategyInterface
{
    public function saveToDatabase($mappedData, $requestData): Response
    {
        try {
            $classes = $mappedData['classes'];
            $imageData = $mappedData['images'];
            // 1. Create Dataset
            $dataset = Dataset::create([
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
            foreach ($classes['names'] as $categoryName) {
                $classIds[] = AnnotationClass::create([
                    'dataset_id' => $dataset->id,
                    'name' => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ])->id;
            }

            // 3. Assign colors to classes
            $this->assignColorsToClasses($classIds);

            // 4. Save Images and Annotations
            $this->saveImageWithAnnotations($imageData, $dataset, $classIds);

            // 5. Save dataset metadata
            foreach ($requestData['metadata'] as $id => $value) {
                DatasetMetadata::create([
                    'dataset_id' => $dataset->id,
                    'metadata_value_id' => $value,
                ]);
            }

            // 6. Save dataset categories
            foreach ($requestData['categories'] as $id) {
                DatasetCategory::create([
                    'dataset_id' => $dataset->id,
                    'category_id' => $id,
                ]);
            }

            return Response::success(data: ['classesToSample' => $classIds]);
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
