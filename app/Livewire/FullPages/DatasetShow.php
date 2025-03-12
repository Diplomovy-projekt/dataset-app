<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering;
    public $uniqueName;
    public $dataset;
    public $searchTerm;
    public Collection $metadata;
    public Collection $categories;
    public array $toggleClasses;
    public string $modalStyle;
    public array $selectedImages = [];
    public int $perPage = 50;

    #[Computed]
    public function paginatedImages()
    {
        $images = $this->fetchImages();
        $preparedImages = $this->prepareImagesForSvgRendering($images);
        return $preparedImages;
    }

    public function mount()
    {
        Util::logStart("dataset show MOUNT");
        $dataset = Dataset::where('unique_name', $this->uniqueName)->with(['classes'])->first();
        if (!$dataset) {
            return redirect()->route('dataset.index');
        }

        $dataset->stats = $dataset->getStats();
        foreach ($dataset->classes as $class) {
            $datasetPath = Util::getDatasetPath($dataset);
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class->id))
                ->first();
            $class->image = [
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class->id,
            ];
        }

        $this->dataset = $dataset->toArray();
        $this->dataset['image_stats'] = Util::getImageSizeStats([$dataset->id]);
        $this->toggleClasses = $dataset['classes']->toArray();
        $this->metadata = $dataset->metadataGroupedByType();
        $this->categories = $dataset->categories()->get();
        Util::logEnd("dataset show MOUNT");

    }

    public function updatedPerPage()
    {
        unset($this->paginatedImages);
    }

    public function search()
    {
        $this->searchTerm = trim($this->searchTerm);
        unset($this->paginatedImages);
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
    public function deleteDataset(DatasetActions $datasetService): void
    {
        $result = $datasetService->deleteDataset($this->uniqueName);
        if($result->isSuccessful()){
            redirect()->route('profile');
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
        Util::logStart("dataset show CACHE QUERY");
        $query = Image::where('dataset_id', $id)->with('annotations.class');
        $payload['query'] = \EloquentSerialize::serialize($query);
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));
        Util::logEnd("dataset show CACHE QUERY");
        Util::logStart("dataset show DISPATCH");
        $this->dispatch('store-download-token', token: $token);
        Util::logEnd("dataset show DISPATCH");
    }

}
