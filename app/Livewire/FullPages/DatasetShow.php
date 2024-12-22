<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\ImageService\ImageTransformer;
use App\Models\Dataset;
use App\Models\Image;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering;
    public $uniqueName;
    public $dataset;
    public $perPage = 50;
    public $categories = [];
    public $searchTerm;
    public $checkedCategories = [];
    #[Computed]
    public function images()
    {
        $images = $this->fetchImages();
        return $this->prepareImagesForSvgRendering($images);
    }
    public function mount($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        $this->dataset = Dataset::where('unique_name', $uniqueName)->first();
        $this->categories = $this->addColorsToClasses($this->dataset->classes);
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-show');

    }

    public function search()
    {
        // Unsetting computed property makes it to recompute, where we fetch images based on search term
        unset($this->images);
    }

    public function toggleCategory($categoryId, $toggleState)
    {
        // TODO ulozit IDcka tych co treba includnut a tych co excludnut, neja rozumne, a nasledne vytvorit
        // query vo fetchImages tak aby to bralo do uvahy

        $this->checkedCategories[$categoryId] = $toggleState;
        //dump($categoryId, $toggleState);
    }

    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('img_filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            return $this->dataset->images()->with(['annotations.class'])->paginate($this->perPage);
        }
    }


}
