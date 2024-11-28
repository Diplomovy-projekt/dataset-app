<?php

namespace App\AnnotationHandler;

use App\AnnotationHandler\Factory\ImportHandlerFactory;
use App\Models\AnnotationCategory;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\DatasetProperty;
use App\Models\Image;
use App\Utils\AppConstants;
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

        return $this->saveDataset($parsedData, $payload, $importHandler);
    }
    public function saveToDatabase($parsedData, $metadata)
    {
        try {
            $categories = $parsedData['categories'];
            $imageData = $parsedData['images'];
            // 1. Create Dataset
            $dataset = Dataset::create([
                'user_id' => auth()->id() ?? "1",
                'display_name' => $metadata['display_name'],
                'unique_name' => $metadata['unique_name'],
                'description' => $metadata['description'] ?? "",
                'num_images' => count($imageData),
                'total_size' => 0,
                'annotation_technique' => $metadata['technique'],
                'is_public' => false,
            ]);

            // 2. Save Categories
            $categoryIds = [];
            foreach ($categories['names'] as $categoryName) {
                $category = AnnotationCategory::create([
                    'dataset_id' => $dataset->id,
                    'name' => $categoryName,
                    'supercategory' => $categories['superCategory'] ?? null,
                ]);
                $categoryIds[] = $category->id;
            }

            // 3. Save Images and Annotations
            foreach ($imageData as $img) {
                $image = Image::create([
                    'dataset_id' => $dataset->id,
                    'img_folder' => $img['img_folder'],
                    'img_filename' => $img['img_filename'],
                    'img_width' => $img['width'],
                    'img_height' => $img['height'],
                ]);

                // 4. Save Annotations
                foreach ($img['annotations'] as $annotation) {
                    AnnotationData::create([
                        'image_id' => $image->id,
                        'annotation_category_id' => $categoryIds[$annotation['class_id']], // map to the correct class_id
                        'center_x' => $annotation['center_x'],
                        'center_y' => $annotation['center_y'],
                        'width' => $annotation['width'],
                        'height' => $annotation['height'],
                        'segmentation' => $annotation['segmentation'],
                    ]);
                }
            }

            // 4. Save dataset properties
            foreach ($metadata['properties'] as $id => $value) {
                DatasetProperty::create([
                    'dataset_id' => $dataset->id,
                    'property_value_id' => $id,
                ]);
            }

            return Response::success("Annotations imported successfully");
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

    private function moveImagesToPublic($folderName, $imageFolder)
    {
        $imageFolderPath = AppConstants::LIVEWIRE_TMP_PATH . $folderName . '/' . $imageFolder;
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
                $destination = AppConstants::DATASETS_PATH . $folderName . '/' . $filename . '.' . $extension;

                try {
                    Storage::move($source, $destination);
                } catch (\Exception $e) {
                    Response::error("An error occurred while moving images to public storage: " . $e->getMessage());
                }
            }
        }

        return Response::success("Images moved to public storage successfully");
    }

    private function saveDataset($parsedData, array $payload, mixed $importHandler)
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
}
