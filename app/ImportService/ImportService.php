<?php

namespace App\ImportService;

use App\Configs\AppConfig;
use App\ImageService\ImageProcessor;
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
    protected $strategy;
    protected $importPreprocessor;
    public function __construct($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws \Exception
     */
    public function handleImport(array $requestData): Response {
        $this->importPreprocessor = new ImportPreprocess($requestData['format']);

        $result = $this->importPreprocessor->preprocessDataset($requestData['unique_name'], $requestData['technique']);
        if (!$result->isSuccessful()) {
            return Response::error($result->message, $result->data);
        }

        $isSaved = $this->strategySpecificSave($result->data, $requestData);
        if (!$isSaved->isSuccessful()) {
            return Response::error($isSaved->message);
        }
        return Response::success("Dataset imported successfully");
    }

    private function strategySpecificSave($mappedData, $requestData): Response
    {
        DB::beginTransaction();

        try {
            // Save the parsed data to the database
            $savedToDb = $this->strategy->saveToDatabase($mappedData, $requestData);
            if (!$savedToDb->isSuccessful()) {
                throw new \Exception($savedToDb->message);
            }

            // Process images
            $processedImages = $this->strategy->processImages($requestData['unique_name'], get_class($this->importPreprocessor->config)::IMAGE_FOLDER);
            if (!$processedImages->isSuccessful()) {
                throw new \Exception($processedImages->message);
            }

            DB::commit();
            return Response::success("Dataset imported successfully");
        } catch (\Exception $e){
            $this->strategy->handleRollback($requestData['unique_name']);
            return Response::error("An unexpected error occurred during the import process: \n".$e->getMessage());
        }
    }

}
