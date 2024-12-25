<?php

namespace App\ImportService;

use App\Configs\AppConfig;
use App\ImageService\DatasetImageProcessor;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Utils\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    private $importHandlerFactory;
    public function __construct()
    {
        $this->importHandlerFactory = new ImportHandlerFactory();
    }

    /**
     * @throws \Exception
     */
    public function handleImport(array $payload): Response {
        $imgProcessor = new DatasetImageProcessor();
        $importHandler = $this->importHandlerFactory->create($payload['format']);
        if ($importHandler instanceof Response) {
            return $importHandler;
        }

        $structureIssues  = $importHandler->findStructureErrors($payload['unique_name']);
        if (!empty($structureIssues)) {
            return Response::error("Zip has invalid structure",$structureIssues);
        }

        $invalidAnnotations = $importHandler->findAnnotationIssues($payload['unique_name'], $payload['technique']);
        if (!empty($invalidAnnotations)) {
            return Response::error("Invalid annotations found",$invalidAnnotations);
        }

        $parsedData = $importHandler->parseDataset($payload['unique_name'], $payload['technique']);
        if (empty($parsedData)) {
            return Response::error("An error occurred while parsing the dataset");
        }

        $isSaved = $this->saveDataset($parsedData, $payload, $importHandler);
        if (!$isSaved->isSuccessful()) {
            return Response::error("An error occurred while saving the dataset: ".$isSaved->message);
        }

        $createdThumbnails = $imgProcessor->createThumbnailsForNewDataset($payload['unique_name']);
        if (!$createdThumbnails->isSuccessful()) {
            return Response::error("An error occurred while creating thumbnails");
        }
        $createdClassCrops = $imgProcessor->createClassCropsForNewDataset($payload['unique_name']);
        if (!$createdClassCrops->isSuccessful()) {
            return Response::error("An error occurred while creating class crops");
        }

        return Response::success("Dataset imported successfully");
    }

    private function saveDataset($parsedData, array $payload, mixed $importHandler): Response
    {
        DB::beginTransaction();

        try {
            // Save the parsed data to the database
            $savedToDb = $this->saveToDatabase($parsedData, $payload);
            if (!$savedToDb->isSuccessful()) {
                DB::rollBack();
                return Response::error("An error occurred while saving to the database". $savedToDb->message);
            }

            // Move images to public storage
            $imagesMoved = $this->moveImagesToPublic($payload['unique_name'], get_class($importHandler)::IMAGE_FOLDER);
            if (!$imagesMoved->isSuccessful()) {
                DB::rollBack();
                return Response::error("An error occurred while moving images to public storage");
            }

            DB::commit();
            return Response::success("Dataset imported successfully");
        } catch (\Exception $e) {

            DB::rollBack();
            return Response::error("An unexpected error occurred during the import process".$e->getMessage());
        }
    }
    public function saveToDatabase($parsedData, $data)
    {
        try {
            $classes = $parsedData['categories'];
            $imageData = $parsedData['images'];
            // 1. Create Dataset
            $dataset = Dataset::create([
                'user_id' => auth()->id() ?? "1",
                'display_name' => $data['display_name'],
                'unique_name' => $data['unique_name'],
                'description' => $data['description'] ?? "",
                'num_images' => count($imageData),
                'total_size' => 0,
                'annotation_technique' => $data['technique'],
                'is_public' => false,
            ]);

            // 2. Save Classes
            $classIds = [];
            foreach ($classes['names'] as $categoryName) {
                $category = AnnotationClass::create([
                    'dataset_id' => $dataset->id,
                    'name' => $categoryName,
                    'supercategory' => $classes['superCategory'] ?? null,
                ]);
                $classIds[] = $category->id;
            }

            // 3. Save Images and Annotations
            foreach ($imageData as $img) {
                $image = Image::create([
                    'dataset_id' => $dataset->id,
                    'img_filename' => $img['img_filename'],
                    'img_width' => $img['width'],
                    'img_height' => $img['height'],
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
            foreach ($data['metadata'] as $id => $value) {
                DatasetMetadata::create([
                    'dataset_id' => $dataset->id,
                    'metadata_value_id' => $id,
                ]);
            }

            // 6. Save dataset categories
            foreach ($data['categories'] as $id) {
                DatasetCategory::create([
                    'dataset_id' => $dataset->id,
                    'category_id' => $id,
                ]);
            }

            return Response::success("Annotations imported successfully");
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }
    private function moveImagesToPublic($folderName, $imageFolder)
    {
        $imageFolderPath = AppConfig::LIVEWIRE_TMP_PATH . $folderName . '/' . $imageFolder;
        $files = Storage::files($imageFolderPath);

        if (empty($files)) {
            return Response::error("No images found in the dataset");
        }

        // Iterate over each file in the folder
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            // Validate file type (only process images with supported extensions)
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $source = $file;
                $destination = AppConfig::DATASETS_PATH . $folderName . '/' . AppConfig::FULL_IMG_FOLDER . $filename . '.' . $extension;

                try {
                    Storage::move($source, $destination);
                } catch (\Exception $e) {
                    Response::error("An error occurred while moving images to public storage: " . $e->getMessage());
                }
            }
        }

        return Response::success("Images moved to public storage successfully");
    }

}
