<?php

namespace App\ExportService;

use App\Configs\AppConfig;
use App\ExportService\Factory\ExportComponentFactory;
use App\FileManagement\ZipManager;
use App\Jobs\DeleteTempFile;
use App\Models\Dataset;
use App\Utils\ImageQuery;
use App\Utils\Response;
use Exception;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    private bool $randomizeAnnotations;
    private array $targetCounts;
    private array $currentCounts;
    protected string $annotationTechnique;

    public function handleExport($payload): Response
    {
        $customDatasetFolder = uniqid('custom_dataset_build_');
        $customDatasetPath = AppConfig::DATASETS_PATH['public'] . $customDatasetFolder;

        try {
            $this->initializeExportParameters($payload);
            $mapper = ExportComponentFactory::createMapper($payload['format']);

            $this->processImageExport($payload, $mapper, $customDatasetFolder);
            $this->finalizeDataset($customDatasetPath);

            return Response::success(data: ['datasetFolder' => $customDatasetFolder.'.zip']);
        }catch (Exception $e) {
            if(Storage::exists($customDatasetPath)) {
                Storage::deleteDirectory($customDatasetPath);
            }
            if(Storage::exists($customDatasetPath.'.zip')) {
                Storage::delete($customDatasetPath.'.zip');
            }
            return Response::error("An error occurred while exporting the dataset: " . $e->getMessage());
        }
    }

    private function initializeExportParameters(array $payload): void
    {
        $this->annotationTechnique = $this->findOutAnnotationTechnique($payload['datasets']);
        $this->randomizeAnnotations = $payload['randomizeAnnotations'] ?? false;
        $this->targetCounts = $payload['targetCounts'];
        $this->currentCounts = array_fill_keys(array_keys($this->targetCounts), 0);
    }

    private function processImageExport(array $payload, $mapper, string $customDatasetFolder): void
    {
        ImageQuery::forDatasets($payload['datasets'])
            ->excludeImages($payload['selectedImages'] ?? [])
            ->filterByClassIds($payload['classIds'] ?? [])
            ->chunkByAnnotations(3, $this->randomizeAnnotations, function ($imagesChunk) use ($mapper, $customDatasetFolder) {
                $filteredChunk = $this->filterAnnotationsInChunk($imagesChunk);

                if (!empty($filteredChunk)) {
                    $mapper->handle($filteredChunk, $customDatasetFolder, $this->annotationTechnique);
                }

                return !$this->isAnnotationTargetReached();
            });
    }

    private function finalizeDataset(string $customDatasetPath): void
    {
        $absolutePath = Storage::path($customDatasetPath);
        ZipManager::createZipFromFolder($absolutePath);
        Storage::deleteDirectory($customDatasetPath);
    }

    public function filterAnnotationsInChunk(array $images): array
    {
        // Iterate over every annotation in every image
        foreach ($images as &$image) {
            $filteredAnnotations = [];

            foreach ($image['annotations'] as $annotation) {
                $className = $annotation['class']['name'];

                // Skips if the target count for this class has been reached or the class is not in the target counts
                if (!isset($this->targetCounts[$className]) || $this->currentCounts[$className] >= $this->targetCounts[$className]) {
                    continue;
                }

                // Add the annotation to the filtered list and increment the count for this class
                $filteredAnnotations[] = $annotation;
                $this->currentCounts[$className]++;
            }

            // Update the image with only the filtered annotations
            $image['annotations'] = $filteredAnnotations;
        }

        unset($image);

        return $this->removeImagesWithNoAnnotations($images);
    }

    private function findOutAnnotationTechnique(mixed $datasets)
    {
        $annotationTechniques = Dataset::whereIn('id', $datasets)
            ->pluck('annotation_technique')
            ->unique();

        // If both bounding box and polygon are present, return bounding box
        if ($annotationTechniques->count() === 2) {
            return AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'];
        } else {
            return $annotationTechniques->first();
        }
    }

    private function isAnnotationTargetReached(): bool
    {
        foreach ($this->targetCounts as $class => $target) {
            if ($this->currentCounts[$class] < $target) {
                return false;
            }
        }
        return true;
    }

    private function removeImagesWithNoAnnotations(array $images): array
    {
        return array_values(array_filter($images, fn($image) => !empty($image['annotations'])));
    }
}
