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

class ExtendDatasetStrategy implements DatasetSavingStrategyInterface
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
                $category = AnnotationClass::updateOrCreate([
                    'dataset_id' => $dataset->id,
                    'name' => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);
                $classIds[] = $category->id;
                if ($category->wasRecentlyCreated) {
                    $classesToSample[] = $category->id; // Collect IDs of newly created records
                }
            }

            // 3. Save Images and Annotations
            foreach ($imageData as $img) {
                $image = Image::create([
                    'dataset_id' => $dataset->id,
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

            return Response::success(data: ['classesToSample' => $classesToSample,]);
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

    public function handleRollback($requestData): void
    {
        // Roll back new images in the htree folders
        foreach ($this->newImages as $image) {
            $fullImg = AppConfig::DATASETS_PATH . $requestData['parent_dataset_unique_name'] . '/' . AppConfig::FULL_IMG_FOLDER . $image;
            $thumbnail = AppConfig::DATASETS_PATH . $requestData['parent_dataset_unique_name'] . '/' . AppConfig::IMG_THUMB_FOLDER . $image;
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
