<?php

namespace App\ImportService\Strategies;

use App\ImageService\ImageProcessor;
use App\ImportService\Interfaces\DatasetSavingStrategyInterface;
use App\Utils\Response;

class ExtendDatasetStrategy implements DatasetSavingStrategyInterface
{
    private $imageProcessor;

    public function __construct()
    {
        // Resolving ImgProcessor from the container
        $this->imageProcessor = app(ImageProcessor::class);
    }

    public function saveToDatabase(array $mappedData, array $data): Response
    {
        // TODO: Implement saveToDatabase() method.
    }

    public function processImages(string $uniqueName, string $imageFolder): Response
    {
        // TODO: Implement processImages() method.
    }

    public function handleRollback(string $uniqueName): void
    {
        // TODO: Implement handleRollback() method.
    }
}
