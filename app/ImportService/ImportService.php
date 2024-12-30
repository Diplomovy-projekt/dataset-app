<?php

namespace App\ImportService;

use App\Configs\AppConfig;
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
    protected $strategy;
    protected $importPreprocessor;
    protected $imageProcessor;
    public function __construct($strategy)
    {
        $this->strategy = $strategy;
        $this->imageProcessor = app(ImageProcessor::class);
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
            $imagesMoved = $this->imageProcessor->moveImagesToPublicDataset(
                sourceFolder: $requestData['unique_name'],
                destinationFolder: $requestData['parent_dataset_unique_name'] ?? null,
                imageFolder: get_class($this->importPreprocessor->config)::IMAGE_FOLDER);
            if (!$imagesMoved->isSuccessful()) {
                throw new DatasetImportException($imagesMoved->message);
            }

            // Create thumbnails
            $thumbnails = $this->imageProcessor->createThumbnails($imagesMoved->data['destinationFolder'], $imagesMoved->data['images']);
            if (count($thumbnails) != count($imagesMoved->data['images'])) {
                throw new DatasetImportException("Failed to create thumbnails for some images");
            }

            //  Create class samples
            $createdClassCrops = $this->createSamplesForClasses($imagesMoved->data['destinationFolder'], $savedToDb->data['classesToSample']);
            if (!$createdClassCrops->isSuccessful()) {
                throw new DatasetImportException($imagesMoved->message);
            }

            DB::commit();
            return Response::success("Dataset imported successfully");
        } catch (DatasetImportException $e){
            $this->strategy->handleRollback($requestData);
            return Response::error($e->getMessage(), $e->getData());
        }
    }

    public function createSamplesForClasses(string $datasetFolder, array $classesToSample): Response
    {
        $dataset = Dataset::where('unique_name', $datasetFolder)->first();
        $batchSize = max(ceil($dataset->num_images * 0.1), 10); // 10% of the dataset size

        $classCounts = [];
        // We are creating crops for classes in batches of 10% images because most likely
        // we will get 3 crops per class sooner than parsing through whole dataset
        for($i = 0; $i < 10; $i++){
            $offset = $i * $batchSize;
            // Fetch images in the batch with annotations belonging to classes to sample
            $images = $dataset->images()
                ->whereHas('annotations', function ($query) use ($classesToSample) {
                    $query->whereIn('annotation_class_id', $classesToSample);
                })
                ->with(['annotations' => function ($query) use ($classesToSample) {
                    $query->whereIn('annotation_class_id', $classesToSample);
                }])
                ->skip($offset)
                ->take($batchSize)
                ->get();

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

}
