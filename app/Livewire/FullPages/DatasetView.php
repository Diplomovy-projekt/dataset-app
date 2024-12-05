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
    public $loadStep = 5;
    public $categoryColors = [];

    public function mount($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        // Load dataset without fetching all images
        $this->dataset = Dataset::where('unique_name', $uniqueName)->first();
        $this->categoryColors = $this->dataset->categories->mapWithKeys(function($category) {
            $category->color = $this->generateRandomRgba(); // Generate random color
            return [$category->id => $category->color]; // Keyed by category ID
        })->toArray();
        $this->loadMore(); // Load the initial batch of images
    }

    private function generateRandomRgba()
    {
        // Random color between 0-255 for r, g, b and alpha between 0.5 and 1
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);

        return "rgb($r, $g, $b)"; // Only RGB, no alpha
    }

    public function loadMore()
    {
        if ($this->dataset) {
            $newImages = $this->dataset
                ->images()
                ->with(['annotations.category'])
                ->skip($this->imagesLoaded)
                ->take($this->loadStep)
                ->get();
            $newImages->each(function ($image) {
                $image->annotations->each(function ($annotation) {
                    // Assign the random color to each annotation based on its category
                    if (isset($this->categoryColors[$annotation->annotation_category_id])) {
                        $annotation->category->color = $this->categoryColors[$annotation->annotation_category_id];
                    }
                });
            });
            $this->images = collect($this->images)->concat($newImages)->toArray();
            $this->imagesLoaded += $newImages->count();
            $this->dispatch('images', images: $newImages->toArray());
        }
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-view');
    }
}
