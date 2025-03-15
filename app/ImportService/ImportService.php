<?php

namespace App\ImportService;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\Exceptions\DatasetImportException;
use App\ImageService\ImageProcessor;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Services\ActionRequestService;
use App\Utils\Response;
use App\Utils\Util;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    use ImageProcessor;
    protected $importPreprocessor;
    private DatasetActions $datasetActions;

    public function __construct()
    {
        $this->datasetActions = new DatasetActions();
    }

    /**
     * @throws Exception
     */
    public function handleImport(array $requestData): Response
    {

        $this->importPreprocessor = new ImportPreprocess($requestData['format']);

        $result = $this->importPreprocessor->preprocessDataset($requestData['unique_name'], $requestData['technique']);

        if (!$result->isSuccessful()) {
            return Response::error($result->message, $result->data);
        }

        $result = $this->saveDataset($result->data, $requestData);

        if (!$result->isSuccessful()) {
            return Response::error($result->message);
        }

        return Response::success("Dataset imported successfully", data: $result->data);
    }

    private function saveDataset($mappedData, $requestData): Response
    {
        DB::beginTransaction();

        try {
            $datasetFolder = $requestData['unique_name'];
            // 1. Move images to full-image folder
            $result = $this->moveImages(
                imageFileNames: array_column($mappedData['images'], 'filename'),
                from: AppConfig::LIVEWIRE_TMP_PATH . $datasetFolder . '/' . $this->importPreprocessor->config::IMAGE_FOLDER,
                to: AppConfig::DATASETS_PATH['private'] . $datasetFolder. '/' . AppConfig::FULL_IMG_FOLDER
            );
            if (!$result->isSuccessful()) {
                throw new DatasetImportException($result->message);
            }

            // 2. Add unique suffixes to image filenames
            $result = $this->datasetActions->addUniqueSuffixes($datasetFolder, $mappedData);
            if(!$result->isSuccessful()){
                throw new DatasetImportException($result->message);
            }
            $imageFilenames = array_column($mappedData['images'], 'filename');

            // 4. Save to DB
            $savedToDb = $this->saveToDatabase($mappedData, $requestData);
            if (!$savedToDb->isSuccessful()) {
                throw new DatasetImportException($savedToDb->message);
            }

            // 3. Create thumbnails
            $thumbnails = $this->createThumbnails($datasetFolder, $imageFilenames);
            if (count($thumbnails) != count($imageFilenames)) {
                throw new DatasetImportException("Failed to create thumbnails for some images");
            }

            // 5. Create class crops
            $createdClassCrops = $this->datasetActions->createSamplesForClasses($datasetFolder,
                $savedToDb->data['classesToSample'],
                $imageFilenames
            );
            if (!$createdClassCrops->isSuccessful()) {
                throw new DatasetImportException($createdClassCrops->message);
            }

            // 6. Assign colors to classes
            $this->datasetActions->assignColorsToClasses(datasetFolder: $requestData['unique_name']);

            DB::commit();
            return Response::success(data: $savedToDb->data['dataset_id']);
        } catch (DatasetImportException $e) {
            if(Storage::exists(AppConfig::DATASETS_PATH['private'] . $requestData['unique_name'])) {
                Storage::deleteDirectory(AppConfig::DATASETS_PATH['private'] . $requestData['unique_name']);
            }
            DB::rollBack();
            return Response::error($e->getMessage(), $e->getData());
        }
    }

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
            foreach ($classes as $class) {
                $classIds[] = AnnotationClass::create([
                    'dataset_id' => $dataset->id,
                    'name' => $class['name'],
                    'supercategory' => $class['superCategory'] ?? null,
                ])->id;
            }
            $this->datasetActions->assignColorsToClasses($classIds);


            // 3. Save Images and Annotations
            foreach ($imageData as $img) {
                $image = Image::create([
                    'dataset_id' => $dataset->id,
                    'dataset_folder' => $dataset->unique_name,
                    'filename' => $img['filename'],
                    'width' => $img['width'],
                    'height' => $img['height'],
                    'size' => $img['size'],
                ]);

                // Save Annotations
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

            // 4. Save dataset metadata
            foreach ($requestData['metadata'] ?? [] as $id => $value) {
                DatasetMetadata::create([
                    'dataset_id' => $dataset->id,
                    'metadata_value_id' => $value,
                ]);
            }

            // 5. Save dataset categories
            foreach ($requestData['categories'] ?? [] as $id) {
                DatasetCategory::create([
                    'dataset_id' => $dataset->id,
                    'category_id' => $id,
                ]);
            }

            return Response::success(data: [
                'classesToSample' => $classIds,
                'newImages' => array_column($mappedData['images'], 'filename'),
                'dataset_id' => $dataset->id,
            ]);
        } catch (\Exception $e) {
            return Response::error("An error occurred while saving to the database ".$e->getMessage());
        }
    }

}
