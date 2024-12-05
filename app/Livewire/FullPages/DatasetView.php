<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetView extends Component
{
    public $uniqueName;
    public $dataset;
    public $images = [];
    public $imagesLoaded = 0;
    public $loadStep = 10;

    public function mount($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        // Load dataset without fetching all images
        $this->dataset = Dataset::where('unique_name', $uniqueName)->first();
        $this->loadMore(); // Load the initial batch of images
    }

    public function loadMore()
    {
        if ($this->dataset) {
            $newImages = $this->dataset
                ->images()
                ->with('annotations')
                ->skip($this->imagesLoaded)
                ->take($this->loadStep)
                ->get();

            $this->images = collect($this->images)->concat($newImages)->toArray();
            $this->imagesLoaded += $newImages->count();
        }
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-view');
    }
}
