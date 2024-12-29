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
    private $imageProcessor;

    public function __construct()
    {
        // Resolving ImgProcessor from the container
        $this->imageProcessor = app(ImageProcessor::class);
    }

    public function saveToDatabase($mappedData, $requestData): Response
    {
        try {
            $classes = $mappedData['categories'];
            $imageData = $mappedData['images'];
            // 1. Create Dataset
            $requestDataset = Dataset::create([
                'user_id' => auth()->id() ?? "1",
                'display_name' => $requestData['display_name'],
                'unique_name' => $requestData['unique_name'],
                'description' => $requestData['description'] ?? "",
                'num_images' => count($imageData),
                'total_size' => 0,
                'annotation_technique' => $requestData['technique'],
                'is_public' => false,
            ]);

            // 2. Save Classes
            $classIds = [];
            foreach ($classes['names'] as $categoryName) {
                $category = AnnotationClass::updateOrCreate([
                    'dataset_id' => $requestDataset->id,
                    'name' => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);
                $classIds[] = $category->id;
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

            return Response::success("Annotations imported successfully", data: $requestDataset);
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

    public function processImages(string $uniqueName, string $imageFolder): Response
    {
        // Move images to public storage
        $imagesMoved = $this->imageProcessor->moveImagesToPublicDataset($uniqueName, $imageFolder);
        if (!$imagesMoved->isSuccessful()) {
            return Response::error($imagesMoved->message);
        }

        // Create thumbnails
        $createdThumbnails = $this->createThumbnailsForNewDataset($uniqueName);
        if (!$createdThumbnails->isSuccessful()) {
            return Response::error($imagesMoved->message);
        }

        //  Create class crops
        $createdClassCrops = $this->createClassCropsForNewDataset($uniqueName);
        if (!$createdClassCrops->isSuccessful()) {
            return Response::error($imagesMoved->message);
        }

        return Response::success();
    }

    public function createThumbnailsForNewDataset(string $datasetFolder): Response
    {
        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConfig::IMG_THUMB_FOLDER);
        $files = Storage::disk('datasets')->files($datasetFolder.'/'.AppConfig::FULL_IMG_FOLDER);

        $thumbnails = $this->imageProcessor->createThumbnails($datasetFolder, $files);
        if (count($thumbnails) == count($files)) {
            return Response::success("Thumbnails created successfully", data: $thumbnails[0]);
        }

        return Response::error("Failed to create thumbnails for some images");
    }

    public function createClassCropsForNewDataset(string $datasetFolder): Response
    {
        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConfig::CLASS_IMG_FOLDER);

        $dataset = Dataset::where('unique_name', $datasetFolder)->first();
        $batchSize = max(ceil($dataset->num_images * 0.1), 1); // 10% of the dataset size

        $classCounts = [];
        // We are creating crops for classes in batches of 10% images because most likely
        // we will get 3 crops per class sooner than parsing through whole dataset
        for($i = 0; $i < 10; $i++){
            $offset = $i * $batchSize;
            $images = $dataset->images()->with('annotations')->skip($offset)->take($batchSize)->get();

            $classCounts = $this->imageProcessor->createClassCrops($datasetFolder, $images, $classCounts);
            if (!in_array(false, array_map(fn($count) => $count['count'] >= 3, $classCounts))) {
                break;
            }

            if ($offset + $batchSize >= $dataset->num_images) {
                break;
            }
        }
        return Response::success("Class crops created successfully");
    }

    public function handleRollback($uniqueName): void
    {
        // Rollback the dataset upload
        if(Storage::disk('datasets')->exists($uniqueName)){
            Storage::disk('datasets')->deleteDirectory($uniqueName);
        }
        DB::rollBack();
    }
}
