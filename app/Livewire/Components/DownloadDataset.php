<?php

namespace App\Livewire\Components;

use App\Configs\AppConfig;
use App\ExportService\ExportService;
use App\Models\AnnotationClass;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class DownloadDataset extends Component
{
    public string $exportDataset = '';
    public string $filePath;
    public mixed $progress;
    public  $failedDownload = null;
    public string $exportFormat = '';
    public string $token = '';
    public array|Collection $classesData = [];
    public array|Collection $originalClassesData = [];
    public array $stats = [];
    public int $minAnnotations;
    public int $maxAnnotations;
    #[Locked]
    public bool $locked = false;
    protected $rules = [
        'exportFormat' => 'required|string',
        'token' => 'required|string',
    ];

    protected $messages = [
        'exportFormat.required' => 'Please select an export format.',
        'token.required' => 'Download token not set. Please wait for data preparation to complete and try again.',
    ];


    #[On('store-download-token')]
    public function storeDownloadToken($token)
    {
        $this->token = $token;
        $this->setClassesData($this->getFromCache());
        $this->calculateStats($this->classesData);
    }

    public function render()
    {
        return view('livewire.components.download-dataset');
    }
    private function getFromCache()
    {
        if (empty($this->token)) {
            $this->failedDownload = [
                'message' => 'Download token not provided',
                'data' => null
            ];
            return [];
        }

        $payload = Cache::get("download_query_{$this->token}");

        if (!$payload) {
            $this->failedDownload = [
                'message' => 'Download request expired or invalid',
                'data' => null
            ];
            return [];
        }
        return $payload;
    }
    public function download()
    {
        $this->validate();
        if ($this->locked) {
            return;
        }
        $this->locked = true;

        $payload = $this->getFromCache();
        $images = $this->getFilteredImages($payload['query']);
        $annotationTechnique = $this->findOutAnnotationTechnique($payload['datasets']);

        $response = $this->exportDataset($images, $annotationTechnique);
        if (!$response) {
            return;
        }

        return $this->streamDownload();
    }
    private function getFilteredImages(string $query): array
    {
        $images = \EloquentSerialize::unserialize($query)->get()->toArray();
        return $this->filterAnnotationsByThreshold($images, $this->classesData, false);
    }
    private function exportDataset(array $images, string $annotationTechnique): bool
    {
        $response = ExportService::handleExport($images, $this->exportFormat, $annotationTechnique);

        if (!$response->isSuccessful()) {
            $this->failedDownload = [
                'message' => $response->message,
                'data' => $response->data
            ];
            $this->unlock();
            return false;
        }

        $this->exportDataset = $response->data['datasetFolder'];
        $this->filePath = storage_path("app/public/datasets/{$this->exportDataset}");

        if (!file_exists($this->filePath)) {
            abort(404, "File not found.");
        }

        return true;
    }
    public function streamDownload()
    {
        $fileSize = filesize($this->filePath);
        $chunkSize = AppConfig::DOWNLOAD_CHUNK_SIZE;
        $this->progress = 0; // Reset progress when starting the download

        return response()->stream(function () use ($chunkSize, $fileSize) {
            $handle = fopen($this->filePath, 'rb');
            $bytesSent = 0;

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                echo $chunk;
                flush();

                $bytesSent += strlen($chunk);
                $this->progress = round(($bytesSent / $fileSize) * 100, 2);
                session()->put("download_progress_{$this->exportDataset}", $this->progress);
            }

            fclose($handle);
            session()->forget("download_progress_{$this->exportDataset}");
            $this->locked = false;
        }, 200, [
            "Content-Type" => "application/zip",
            "Content-Length" => $fileSize,
            "Content-Disposition" => "attachment; filename=\"{$this->exportDataset}\"",
            "Cache-Control" => "no-cache",
            "Connection" => "keep-alive",
        ]);
    }

    public function updateProgress()
    {
        $progress = session()->get("download_progress_{$this->exportDataset}", 0);
    }

    private function setClassesData(mixed $payload)
    {
        $classIds = $payload['classIds'] ?? Dataset::find($payload['datasets'][0])->classes->pluck('id')->toArray();
        $excludedImages = $payload['selectedImages'] ?? [];
        $datasets = $payload['datasets'];

        $this->originalClassesData = AnnotationClass::whereIn('id', $classIds)
            ->withCount([
                'annotations as annotation_count' => function ($query) use ($datasets, $excludedImages) {
                    $query->whereHas('image', function ($q) use ($datasets, $excludedImages) {
                        $q->whereIn('dataset_id', $datasets)
                            ->whereNotIn('id', $excludedImages);
                    });
                }
            ])
            ->get(['id', 'name', 'supercategory', 'rgb', 'dataset_id'])
            ->groupBy('name')
            ->map(function ($classGroup) {
                $class = $classGroup->first();

                $annotationCount = $classGroup->sum('annotation_count');
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'supercategory' => $class->supercategory,
                    'rgb' => $class->rgb,
                    'count' => $annotationCount,
                    'dataset_id' => $class->dataset_id,
                ];
            })->values();

        $this->originalClassesData = $this->originalClassesData->toArray();
        foreach ($this->originalClassesData as &$class) {
            $datasetPath = Util::getDatasetPath($class['dataset_id']);
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class['id']))->first();

            $class['image'] = [
                'dataset' => basename($datasetPath),
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class['id'],
            ];
        }
        unset($class); // Unset reference to avoid unexpected behavior
        $this->maxAnnotations = max(array_column($this->originalClassesData, 'count'));
        $this->minAnnotations = min(array_column($this->originalClassesData, 'count'));
        $this->classesData = $this->originalClassesData;
    }

    private function adjustClassesDataForThresholds(): void
    {
        $this->classesData = array_values(array_map(
            function ($class) {
                $class['count'] = min($class['count'], $this->maxAnnotations);
                return $class;
            },
            array_filter($this->originalClassesData, fn($class) => $class['count'] >= $this->minAnnotations)
        ));
    }

    public function updatedMinAnnotations(): void
    {
        $this->adjustClassesDataForThresholds();
        $this->calculateStats($this->classesData);
    }

    public function updatedMaxAnnotations(): void
    {
        $this->adjustClassesDataForThresholds();
        $this->calculateStats($this->classesData);
    }

    /**
     * Filter images with annotations based on pre-filtered class data with randomization
     *
     * @param array $images Array of images with annotations from the query
     * @param array $classesData Array of already filtered classes with final counts
     * @param bool $randomizeImages Whether to randomize the images order
     * @param bool $randomizeAnnotations Whether to randomize annotations within each class
     * @return array Filtered images with annotations
     */
    function filterAnnotationsByThreshold(
        array $images,
        array $classesData,
        bool $randomizeAnnotations = true
    ): array
    {
        $targetCounts = [];
        foreach ($classesData as $classData) {
            $targetCounts[$classData['name']] = $classData['count'];
        }

        if (empty($targetCounts)) {
            return [];
        }

        // Group annotations by class for randomized selection
        if ($randomizeAnnotations) {
            shuffle($images);
            $annotationsByClass = [];

            // Initialize the classes
            foreach (array_keys($targetCounts) as $classId) {
                $annotationsByClass[$classId] = [];
            }

            // Collect all annotations by class
            foreach ($images as $imageIndex => $image) {
                foreach ($image['annotations'] as $annotationIndex => $annotation) {
                    $classId = $annotation['class']['id'];

                    if (isset($targetCounts[$classId])) {
                        $annotationsByClass[$classId][] = [
                            'image_index' => $imageIndex,
                            'annotation' => $annotation
                        ];
                    }
                }
            }

            // Randomize annotations within each class and take only what we need
            foreach ($annotationsByClass as $classId => &$annotations) {
                shuffle($annotations);
                $annotations = array_slice($annotations, 0, $targetCounts[$classId]);
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
        } else {
            $currentCounts = array_fill_keys(array_keys($targetCounts), 0);

            foreach ($images as &$image) {
                $filteredAnnotations = [];

                foreach ($image['annotations'] as $annotation) {
                    $className = $annotation['class']['name'];

                    if (!isset($targetCounts[$className]) || $currentCounts[$className] >= $targetCounts[$className]) {
                        continue;
                    }
                    $filteredAnnotations[] = $annotation;
                    $currentCounts[$className]++;
                }

                $image['annotations'] = $filteredAnnotations;
            }
            unset($image);
        }

        // Remove images with no annotations
        $result = array_values(array_filter($images, function($image) {
            return !empty($image['annotations']);
        }));

        return $result;
    }

    private function calculateStats(array $classesData): void
    {
        $this->stats = [
            'totalCount' => $this->calculateTotalCount(),
            'classCount' => count($classesData),
            'maxClass' => $this->findMaxClass(),
            'minClass' => $this->findMinClass(),
            'avgCount' => $this->calculateAverage(),
            'median' => $this->calculateMedian(),
            'stdDev' => $this->calculateStdDev(),
            'imbalance' => $this->calculateImbalance(),
        ];
    }

    private function calculateTotalCount(): int
    {
        $counts = array_column($this->classesData, 'count');
        return array_sum($counts);
    }

    private function findMaxClass(): array
    {
        $counts = array_column($this->classesData, 'count');
        $idk0 = max($counts);
        $idk = array_search(max($counts), $counts);
        $idk2 = $this->classesData[array_search(max($counts), $counts)];
        return $this->classesData[array_search(max($counts), $counts)];
    }

    private function findMinClass(): array
    {
        $counts = array_column($this->classesData, 'count');
        return $this->classesData[array_search(min($counts), $counts)];
    }

    private function calculateAverage(): int
    {
        $counts = array_column($this->classesData, 'count');
        return round(array_sum($counts) / count($counts));
    }

    private function calculateMedian(): int
    {
        $counts = array_column($this->classesData, 'count');
        sort($counts);
        $count = count($counts);
        return $count % 2 === 0
            ? ($counts[$count / 2] + $counts[$count / 2 - 1]) / 2
            : $counts[floor($count / 2)];
    }

    private function calculateStdDev(): int
    {
        $counts = array_column($this->classesData, 'count');
        $mean = $this->calculateAverage();
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $counts)) / count($counts);
        return round(sqrt($variance));
    }

    private function calculateImbalance(): float
    {
        $counts = array_column($this->classesData, 'count');
        return round(max($counts) / max(1, min($counts)), 1);
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

}
