<?php

namespace App\Livewire\FullPages;

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
    public $perPage = 50;
    public $searchTerm;
    public $classes = [];
    public $metadata = [];
    public $categories = [];
    #[Computed]
    public function images()
    {
        $images = $this->fetchImages();
        return $this->prepareImagesForSvgRendering($images);
    }
    public function mount($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        $this->dataset = Dataset::where('unique_name', $uniqueName)->with(['classes'])->first();
        $this->metadata = $this->dataset->metadataGroupedByType();
        $this->categories = $this->dataset->categories()->get();
        $this->classes = $this->addColorsAndStateToClasses($this->dataset->classes);
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

    public function toggleClass($categoryId, $toggleState)
    {
        switch ($toggleState) {
            case 'all':
                $this->classes = array_map(function ($class) {
                    $class['state'] = 'true'; // Set state to 'true'
                    return $class;
                }, $this->classes);
                break;

            case 'none':
                $this->classes = array_map(function ($class) {
                    $class['state'] = 'false'; // Set state to 'false'
                    return $class;
                }, $this->classes);
                break;
            default:
                if ($toggleState == 'true') {
                    $this->classes[$categoryId]['state'] = 'true';
                }
                else {
                    $this->classes[$categoryId]['state'] = 'false';
                }
        }
        unset($this->images);
    }

    private function fetchImages()
    {

        if ($this->searchTerm) {
            return Image::where('img_filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            $activeClassIds = collect($this->classes)
                ->where('state', 'true')  // Filter classes where state is 'true'
                ->pluck('id')             // Get the IDs of those classes
                ->toArray();
            return $this->dataset->images()
                ->with(['annotations' => fn($query) => $query->whereIn('annotation_class_id', $activeClassIds)->with('class')])
                ->paginate($this->perPage);
        }
    }

}
