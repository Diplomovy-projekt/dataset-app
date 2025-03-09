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
use App\Utils\Util;
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
    public function handleImport(array $requestData): Response
    {

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
            // 1. Move images to full-image folder
            $result = $this->moveFullImages(
                imageFileNames: array_column($mappedData['images'], 'filename'),
                sourceFolder: $requestData['unique_name'] . '/' . $this->importPreprocessor->config::IMAGE_FOLDER,
                destinationFolder: $requestData['parent_dataset_unique_name'] ?? $requestData['unique_name']
            );
            if (!$result->isSuccessful()) {
                throw new DatasetImportException($result->message);
            }
            $datasetFolder = $result->data['datasetFolder'];

            // 2. Add unique suffixes to image filenames
            $result = $this->strategy->addUniqueSuffixes($datasetFolder, $mappedData);
            if(!$result->isSuccessful()){
                throw new DatasetImportException($result->message);
            }
            $imageFilenames = array_column($mappedData['images'], 'filename');

            // 3. Create thumbnails
            $thumbnails = $this->createThumbnails($datasetFolder, $imageFilenames);
            if (count($thumbnails) != count($imageFilenames)) {
                throw new DatasetImportException("Failed to create thumbnails for some images");
            }

            // 4. Save to DB
            $savedToDb = $this->strategy->saveToDatabase($mappedData, $requestData);
            if (!$savedToDb->isSuccessful()) {
                throw new DatasetImportException($savedToDb->message);
            }

            // 5. Create class crops
            $datasetService = new DatasetActions();
            $createdClassCrops = $datasetService->createSamplesForClasses($datasetFolder,
                $savedToDb->data['classesToSample'],
                $imageFilenames
            );
            if (!$createdClassCrops->isSuccessful()) {
                throw new DatasetImportException($createdClassCrops->message);
            }

            DB::commit();
            return Response::success();
        } catch (DatasetImportException $e) {
            $this->strategy->handleRollback($requestData);
            return Response::error($e->getMessage(), $e->getData());
        }
    }

}
