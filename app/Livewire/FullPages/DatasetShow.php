<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\Image;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
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
    public mixed $progress = 0;
    public array $failedDownload = [];
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
        $result = $datasetService->deleteDataset($this->uniqueName);
        if($result->isSuccessful()){
            return redirect()->route('profile');
        }
    }

    public function deleteImages(DatasetActions $datasetService)
    {
        $result = $datasetService->deleteImages($this->uniqueName, $this->selectedImages);
        if($result->isSuccessful()){
            $this->mount();
        }
    }

    public function cacheQuery($id)
    {
        $query = Image::where('dataset_id', $id)->with('annotations.class');

        $token = Str::random(32);
        Cache::put("download_query_{$token}", \EloquentSerialize::serialize($query), now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

    public function updateProgress()
    {
        $this->progress = session()->get("download_progress_{$this->exportDataset}", 0);

    }

}
