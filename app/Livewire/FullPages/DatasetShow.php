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
    #[Computed]
    public function images()
    {
        $images = $this->fetchImages();
        return $this->prepareImagesForSvgRendering($images, $this->dataset['classes']);
    }
    public function mount()
    {
        $dataset = Dataset::where('unique_name', $this->uniqueName)->with(['classes'])->first();
        if (!$dataset) {
            session()->flash('error', 'Dataset not found.');
            return redirect()->route('dataset.index');
        }
        $classes = $this->addColorsAndStateToClasses($dataset->classes);

        $dataset->annotationCount = $dataset->annotations()->count();
        $this->dataset = $dataset->toArray();
        $this->dataset['classes'] = $classes;
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
            return Image::where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            $activeClassIds = collect($this->dataset['classes'])
                ->where('state', 'true')  // Filter classes where state is 'true'
                ->pluck('id')             // Get the IDs of those classes
                ->toArray();
            $dataset = Dataset::where('unique_name', $this->uniqueName)->first();
            return $dataset->images()
                ->with(['annotations' => fn($query) => $query->whereIn('annotation_class_id', $activeClassIds)->with('class')])
                ->paginate($this->perPage);
        }
    }
    public function deleteDataset(DatasetActions $datasetService)
    {
        $result = $datasetService->deleteDataset($this->uniqueName);
        if($result->isSuccessful()){
            return redirect()->route('profile');
        }
    }

    public function deleteImages(DatasetActions $datasetService, $ids)
    {
        $result = $datasetService->deleteImages($this->uniqueName, $ids);
        if($result->isSuccessful()){
            $this->mount();
        }
    }

}
