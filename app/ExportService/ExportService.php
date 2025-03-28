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
            ->chunkByAnnotations(3, function ($imagesChunk) use ($mapper, $customDatasetFolder) {
                $filteredChunk = $this->filterAnnotationsInChunk($imagesChunk);

                if (!empty($filteredChunk)) {
                    $mapper->handle($filteredChunk, $customDatasetFolder, $this->annotationTechnique);
                }

                return !$this->isAnnotationTargetReached($this->currentCounts);
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
        return $this->randomizeAnnotations
            ? $this->filterRandomizedAnnotations($images)
            : $this->filterSequentialAnnotations($images);
    }

    private function filterRandomizedAnnotations(array $images): array
    {
        shuffle($images);
        $annotationsByClass = [];

        // Initialize the classes
        foreach (array_keys($this->targetCounts) as $className) {
            $annotationsByClass[$className] = [];
        }

        // Collect all annotations by class
        foreach ($images as $imageIndex => $image) {
            foreach ($image['annotations'] as $annotation) {
                $className = $annotation['class']['name'];

                if (isset($this->targetCounts[$className])) {
                    $annotationsByClass[$className][] = [
                        'image_index' => $imageIndex,
                        'annotation' => $annotation
                    ];
                }
            }
        }

        // Randomize annotations within each class and take only what we need
        foreach ($annotationsByClass as $className => &$annotations) {
            shuffle($annotations);
            $annotations = array_slice($annotations, 0, $this->targetCounts[$className]);
        }
        unset($annotations);

        // Clear all annotations from images first
        foreach ($images as &$image) {
            $image['annotations'] = [];
        }
        unset($image);

        // Add selected annotations back to their respective images
        foreach ($annotationsByClass as $classId => $annotations) {
            foreach ($annotations as $item) {
                $images[$item['image_index']]['annotations'][] = $item['annotation'];
            }
        }

        // Remove images with no annotations
        return array_values(array_filter($images, fn($image) => !empty($image['annotations'])));
    }

    private function filterSequentialAnnotations(array $images): array
    {
        foreach ($images as &$image) {
            $filteredAnnotations = [];

            foreach ($image['annotations'] as $annotation) {
                $className = $annotation['class']['name'];

                if (!isset($this->targetCounts[$className]) || $this->currentCounts[$className] >= $this->targetCounts[$className]) {
                    continue;
                }
                $filteredAnnotations[] = $annotation;
                $this->currentCounts[$className]++;
            }

            $image['annotations'] = $filteredAnnotations;
        }
        unset($image);

        // Remove images with no annotations
        return array_values(array_filter($images, fn($image) => !empty($image['annotations'])));
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

    private function isAnnotationTargetReached(array $currentCounts) {
        return array_reduce($currentCounts, function($carry, $count) {
            return $carry && $count > 0;
        }, true);
    }
}
