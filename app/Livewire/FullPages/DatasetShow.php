<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\Image;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
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
        foreach($dataset->classes as $class) {
            $firstFile = collect(Storage::disk('datasets')
                ->files($dataset->unique_name . '/' . AppConfig::CLASS_IMG_FOLDER . $class->id))->first();
            $class->image = AppConfig::LINK_DATASETS_PATH . $firstFile;
        }
        $this->dataset = $dataset->toArray();
        $this->toggleClasses = $dataset['classes']->toArray();
        $this->metadata = $dataset->metadataGroupedByType();
        $this->categories = $dataset->categories()->get();
    }

    public function search()
    {
        $this->searchTerm = trim($this->searchTerm);
        unset($this->images);
    }
    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('dataset_id', $this->dataset['id'])->where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate(AppConfig::PER_PAGE);
        }
        else {
            return Image::where('dataset_id', $this->dataset['id'])->with(['annotations.class'])->paginate(AppConfig::PER_PAGE);
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
        $query = Image::where('dataset_id', $id)->with('annotations.class');

        $token = Str::random(32);
        Cache::put("download_query_{$token}", \EloquentSerialize::serialize($query), now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

}
