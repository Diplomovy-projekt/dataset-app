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
use App\Utils\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    use ImageProcessor;
    protected $strategy;
    protected $importPreprocessor;
    public function __construct($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws Exception
     */
    public function handleImport(array $requestData): Response {
        $this->importPreprocessor = new ImportPreprocess($requestData['format']);

        $result = $this->importPreprocessor->preprocessDataset($requestData['unique_name'], $requestData['technique']);
        if (!$result->isSuccessful()) {
            return Response::error($result->message, $result->data);
        }

        $isSaved = $this->saveDataset($result->data, $requestData);
        if (!$isSaved->isSuccessful()) {
            return Response::error($isSaved->message);
        }
        return Response::success("Dataset imported successfully");
    }

    private function saveDataset($mappedData, $requestData): Response
    {
        DB::beginTransaction();

        try {
            // Save the mapped data to the database
            $savedToDb = $this->strategy->saveToDatabase($mappedData, $requestData);
            if (!$savedToDb->isSuccessful()) {
                throw new DatasetImportException($savedToDb->message);
            }

            // Move images to public storage
            $imagesMoved = $this->moveImagesToPublicDataset(
                sourceFolder: $requestData['unique_name'],
                imageFolder: get_class($this->importPreprocessor->config)::IMAGE_FOLDER,
                destinationFolder: $requestData['parent_dataset_unique_name'] ?? null);
            if (!$imagesMoved->isSuccessful()) {
                throw new DatasetImportException($imagesMoved->message);
            }

            // Create thumbnails
            $thumbnails = $this->createThumbnails($imagesMoved->data['destinationFolder'], $imagesMoved->data['images']);
            if (count($thumbnails) != count($imagesMoved->data['images'])) {
                throw new DatasetImportException("Failed to create thumbnails for some images");
            }

            //  Create class samples
            $datasetService = new DatasetActions();
            $createdClassCrops = $datasetService->createSamplesForClasses($imagesMoved->data['destinationFolder'],
                                                                          $savedToDb->data['classesToSample'],
                                                                          $savedToDb->data['newImages']);
            if (!$createdClassCrops->isSuccessful()) {
                throw new DatasetImportException($imagesMoved->message);
            }

            DB::commit();
            return Response::success();
        } catch (DatasetImportException $e){
            $this->strategy->handleRollback($requestData);
            return Response::error($e->getMessage(), $e->getData());
        }
    }

}
