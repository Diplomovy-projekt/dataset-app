<?php

namespace App\Livewire\Components;

use App\Configs\AppConfig;
use App\ExportService\ExportService;
use App\Models\AnnotationClass;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\ImageQuery;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DownloadDataset extends Component
{
    public string $exportDataset = '';
    #[Locked]
    public string $filePath;
    public mixed $progress;
    #[Locked]
    public  $failedDownload = null;
    public string $exportFormat = '';
    #[Locked]
    public string $token = '';
    public array|Collection $classesData = [];
    public array|Collection $originalClassesData = [];
    public array $stats = [];
    public int $minAnnotations;
    public int $maxAnnotations;
    public bool $randomizeAnnotations = false;
    public bool $locked = false;
    public bool $processing = false;
    public bool $processingCompleted = false;
    public bool $resetTrigger = false;
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
        $this->resetState();
        $this->failedDownload = null;
        $this->token = $token;
        $this->setClassesData($this->getFromCache());
        if($this->classesData) {
            $this->calculateStats($this->classesData);
        } else {
            $this->failedDownload = [
                'message' => 'No Images and Classes found in the dataset',
                'data' => null
            ];
        }
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
        if (!$this->validateExport()) {
            $this->resetState();
            return;
        }

        $this->failedDownload = null;
        $this->processing = true;
        $this->dispatch('processing-updated', true);

        $payload = $this->getFromCache();
        if (empty($payload)) {
            $this->resetState();
            return;
        }

        $response = $this->exportDataset($payload);
        if (!$response) {
            $this->resetState();
            return;
        }

        session(['download_file_path' => $this->filePath]);

        // Reset processing state after completing export
        $this->processingCompleted = true;
    }

    private function exportDataset(array $payload): bool
    {
        $targetCounts = array_column($this->classesData, 'count', 'name');
        $payload['targetCounts'] = $targetCounts;
        $payload['randomizeAnnotations'] = $this->randomizeAnnotations;
        $payload['format'] = $this->exportFormat;

        $exportService = app(ExportService::class);
        $response = $exportService->handleExport($payload);

        if (!$response->isSuccessful()) {
            $this->failedDownload = [
                'message' => $response->message,
                'data' => $response->data
            ];
            $this->resetState();
            return false;
        }

        $this->exportDataset = $response->data['datasetFolder'];
        $this->filePath = storage_path("app/public/datasets/{$this->exportDataset}");

        if (!file_exists($this->filePath)) {
            abort(404, "File not found.");
        }

        return true;
    }
    private function resetState()
    {
        $this->resetTrigger = !$this->resetTrigger;
        $this->processing = false;
        $this->processingCompleted = false;
        $this->locked = false;
    }
    public function validateExport()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->resetState();
            throw $e;
        }

        if ($this->locked) {
            $this->resetState();
            $this->addError('locked', 'The process is currently locked. Please wait.');
            return false;
        }

        if ($this->minAnnotations > $this->maxAnnotations) {
            $this->resetState();
            $this->addError('annotations', 'Minimum annotations cannot be greater than maximum annotations.');
            return false;
        }

        return true;
    }


    private function setClassesData(mixed $payload)
    {
        $classIds = $payload['classIds'] ?? Dataset::find($payload['datasets'][0])->classes->pluck('id')->toArray();
        $excludedImages = $payload['selectedImages'] ?? [];
        $datasets = $payload['datasets'];

        if(empty($classIds)) {
            return;
        }
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
        unset($class);
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
}
