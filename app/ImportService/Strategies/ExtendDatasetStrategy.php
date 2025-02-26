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

class ExtendDatasetStrategy extends BaseStrategy implements DatasetSavingStrategyInterface
{
    private $newImages = [];
    public function saveToDatabase($mappedData, $requestData): Response
    {
        try {
            $classes = $mappedData['classes'];
            $imageData = $mappedData['images'];
            $this->newImages = array_column($imageData, 'filename');
            // 1. Dataset update
            $dataset = Dataset::where('unique_name', $requestData['parent_dataset_unique_name'])->first();
            $dataset->num_images += count($imageData);
            $dataset->total_size += array_sum(array_column($imageData, 'size'));
            $dataset->save();

            // 2. Save New Classes
            $classIds = [];
            $classesToSample = [];
            foreach ($classes['names'] as $categoryName) {
                $class = AnnotationClass::updateOrCreate([
                    'dataset_id'   => $dataset->id,
                    'name'         => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);
                $classIds[] = $class->id;
                if ($class->wasRecentlyCreated) {
                    $classesToSample[] = $class->id;
                }
                // Check if the sample count is low and add the class
                $filesCount = count(Storage::disk('datasets')->files(
                    "{$dataset->unique_name}/" . AppConfig::CLASS_IMG_FOLDER . "/{$class->id}"
                ));
                if ($filesCount < AppConfig::SAMPLES_COUNT) {
                    $classesToSample[] = $class->id;
                }
            }

            // 3. Assign colors to classes
            $this->assignColorsToClasses($classIds);

            // 4. Save Images and Annotations
            $this->saveImageWithAnnotations($imageData, $dataset, $classIds);

            return Response::success(data: ['classesToSample' => $classesToSample,
                                            'newImages' => $this->newImages]);
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

    public function handleRollback($requestData): void
    {
        // Roll back new images in the htree folders
        foreach ($this->newImages as $image) {
            $fullImg = AppConfig::DATASETS_PATH['public'] . $requestData['parent_dataset_unique_name'] . '/' . AppConfig::FULL_IMG_FOLDER . $image;
            $thumbnail = AppConfig::DATASETS_PATH['public'] . $requestData['parent_dataset_unique_name'] . '/' . AppConfig::IMG_THUMB_FOLDER . $image;
            if(Storage::disk('storage')->exists($fullImg)){
                Storage::disk('storage')->delete($fullImg);
            }
            if(Storage::disk('storage')->exists($thumbnail)){
                Storage::disk('storage')->delete($thumbnail);
            }
        }
        DB::rollBack();
    }
}
