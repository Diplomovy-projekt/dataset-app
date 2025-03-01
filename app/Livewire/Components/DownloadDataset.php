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
    public array|Collection $annotationCount = [];
    public array|Collection $originalAnnotationCount = [];
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
        $this->setAnnotationCount($this->getFromCache());
        $this->calculateStats($this->annotationCount);
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
        if($this->locked) {
            return;
        }
        $this->locked = true;

        $payload = $this->getFromCache();

        $images = \EloquentSerialize::unserialize($payload['query'])->get()->toArray();
        $response = ExportService::handleExport($images, $this->exportFormat);
        if(!$response->isSuccessful()) {
            $this->failedDownload = [
                'message' => $response->message,
                'data' => $response->data
            ];
            return;
        }
        $this->exportDataset = $response->data['datasetFolder'];
        $this->filePath = storage_path("app/public/datasets/{$this->exportDataset}");

        if (!file_exists($this->filePath)) {
            abort(404, "File not found.");
        }

        $fileSize = filesize($this->filePath);
        $chunkSize = 1024 * 1024; // 1MB per chunk
        $bytesSent = 0;

        $this->progress = 0; // Reset progress when starting the download

        return response()->stream(function () use ($chunkSize, &$bytesSent, $fileSize) {
            $handle = fopen($this->filePath, 'rb');

            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                echo $chunk;
                flush();

                $bytesSent += strlen($chunk); // Track actual bytes read
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

        if ($progress < 100) {
            $this->progress = $progress;
        } else {
            $this->progress = 100;
        }
    }

    private function setAnnotationCount(mixed $payload)
    {
        $classIds = $payload['classIds'] ?? Dataset::find($payload['datasets'][0])->classes->pluck('id')->toArray();
        $excludedImages = $payload['selectedImages'] ?? [];
        $datasets = $payload['datasets'];

        $this->originalAnnotationCount = AnnotationClass::whereIn('id', $classIds)
            ->withCount([
                'annotations as annotation_count' => function ($query) use ($datasets, $excludedImages) {
                    $query->whereHas('image', function ($q) use ($datasets, $excludedImages) {
                        $q->whereIn('dataset_id', $datasets)
                            ->whereNotIn('id', $excludedImages);
                    });
                }
            ])
            ->get(['id', 'name', 'supercategory', 'rgb', 'dataset_id'])
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'supercategory' => $class->supercategory,
                    'rgb' => $class->rgb,
                    'count' => $class->annotation_count,
                    'dataset_id' => $class->dataset_id,
                ];
            });
        $this->originalAnnotationCount = $this->originalAnnotationCount->toArray();
        foreach ($this->originalAnnotationCount as &$class) { // Add &
            $datasetPath = Util::getDatasetPath($class['dataset_id']);
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class['id']))->first();

            $class['image'] = [
                'dataset' => basename($datasetPath),
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class['id'],
            ];
        }
        unset($class); // Unset reference to avoid unexpected behavior
        $this->maxAnnotations = max(array_column($this->originalAnnotationCount, 'count'));
        $this->minAnnotations = min(array_column($this->originalAnnotationCount, 'count'));
        $this->annotationCount = $this->originalAnnotationCount;
    }

    private function adjustAnnotationCountForThresholds(): void
    {
        if (empty($this->originalAnnotationCount)) {
            return;
        }

        $this->annotationCount = array_values(array_map(
            function ($class) {
                $class['count'] = min($class['count'], $this->maxAnnotations);
                return $class;
            },
            array_filter($this->originalAnnotationCount, fn($class) => $class['count'] >= $this->minAnnotations)
        ));
    }

    public function updatedMinAnnotations(): void
    {
        $this->adjustAnnotationCountForThresholds();
        $this->calculateStats($this->annotationCount);
    }

    public function updatedMaxAnnotations(): void
    {
        $this->adjustAnnotationCountForThresholds();
        $this->calculateStats($this->annotationCount);
    }
    private function calculateStats(array $annotationCount): void
    {
        $this->stats = [
            'totalCount' => $this->calculateTotalCount(),
            'classCount' => count($annotationCount),
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
        $counts = array_column($this->annotationCount, 'count');
        return array_sum($counts);
    }

    private function findMaxClass(): array
    {
        $counts = array_column($this->annotationCount, 'count');
        return $this->annotationCount[array_search(max($counts), $counts)];
    }

    private function findMinClass(): array
    {
        $counts = array_column($this->annotationCount, 'count');
        return $this->annotationCount[array_search(min($counts), $counts)];
    }

    private function calculateAverage(): int
    {
        $counts = array_column($this->annotationCount, 'count');
        return round(array_sum($counts) / count($counts));
    }

    private function calculateMedian(): int
    {
        $counts = array_column($this->annotationCount, 'count');
        sort($counts);
        $count = count($counts);
        return $count % 2 === 0
            ? ($counts[$count / 2] + $counts[$count / 2 - 1]) / 2
            : $counts[floor($count / 2)];
    }

    private function calculateStdDev(): int
    {
        $counts = array_column($this->annotationCount, 'count');
        $mean = $this->calculateAverage();
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $counts)) / count($counts);
        return round(sqrt($variance));
    }

    private function calculateImbalance(): float
    {
        $counts = array_column($this->annotationCount, 'count');
        return round(max($counts) / max(1, min($counts)), 1);
    }

}
