<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\ImageService\ImageTransformer;
use App\Jobs\DeleteTempFile;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\Image;
use App\Models\MetadataValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering;
    public $uniqueName;
    public $dataset;
    private $perPage = 25;
    public $searchTerm;
    public $metadata = [];
    public $categories = [];
    public $modalStyle;
    public $selectedImages = [];
    private string $exportDataset = '';
    public $exportFormat = '';
    public $availableFormats = AppConfig::ANNOTATION_FORMATS_INFO;
    public mixed $progress;
    public array $failedDownload = [];
    public string $downloadLink = '';
    public string $filePath = '';

    #[Computed]
    public function paginatedImages()
    {
        $images = $this->fetchImages();
        return $this->prepareImagesForSvgRendering($images);
    }
    public function mount()
    {
        $dataset = Dataset::where('unique_name', $this->uniqueName)->with(['classes'])->first();
        if (!$dataset) {
            session()->flash('error', 'Dataset not found.');
            return redirect()->route('dataset.index');
        }

        $dataset->stats = $dataset->getStats();
        $this->dataset = $dataset->toArray();
        $this->metadata = $dataset->metadataGroupedByType();
        $this->categories = $dataset->categories()->get();
    }

    public function search()
    {
        // Unsetting computed metadata makes it to recompute, where we fetch images based on search term
        unset($this->images);
    }

    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('dataset_id', $this->dataset['id'])->where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            return Image::where('dataset_id', $this->dataset['id'])->with(['annotations.class'])->paginate($this->perPage);
        }
    }
    public function deleteDataset(DatasetActions $datasetService)
    {
        Gate::authorize('delete-dataset', $this->dataset['id']);
        $result = $datasetService->deleteDataset($this->uniqueName);
        if($result->isSuccessful()){
            return redirect()->route('profile');
        }
    }

    public function deleteImages(DatasetActions $datasetService)
    {
        Gate::authorize('delete-dataset', $this->dataset['id']);
        $result = $datasetService->deleteImages($this->uniqueName, $this->selectedImages);
        if($result->isSuccessful()){
            $this->mount();
        }
    }

    public function startDownload()
    {
        $images = Image::where('dataset_id', $this->dataset['id'])->with(['annotations.class'])->get();
        $response = ExportService::handleExport($images, $this->exportFormat);
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
                $bytesSent += $chunkSize;
                // Update the progress in session
                $this->progress = round(($bytesSent / $fileSize) * 100, 2);
                session()->put("download_progress_{$this->filePath}", $this->progress);
            }

            fclose($handle);
            session()->forget("download_progress_{$this->filePath}");
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
        // Get the current progress from session
        $progress = session()->get("download_progress_{$this->exportDataset}", 0);

        // Update the frontend progress
        if ($progress < 100) {
            $this->progress = $progress;
        } else {
            $this->progress = 100; // Ensure it's 100% when done
        }
    }

}
