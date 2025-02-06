<?php

namespace App\Livewire\FullPages;

use App\DatasetActions\DatasetActions;
use App\ImageService\ImageRendering;
use App\ImageService\ImageTransformer;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\Image;
use App\Models\MetadataValue;
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

    public function render()
    {
        return view('livewire.full-pages.dataset-show');
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

}
