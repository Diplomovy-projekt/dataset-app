<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetView extends Component
{
    use WithPagination;
    public $uniqueName;
    public $dataset;
    public $imagesLoaded = 0;
    public $loadStep = 5;
    public $categories = [];
    public $newlyLoadedImages = [];
    #[Computed]
    public function images()
    {
        $images = $this->dataset->images()->with(['annotations.category'])->paginate(10);

        //Add color to each annotation based on category
        $images->each(function ($image) {
            $image->annotations->each(function ($annotation) {
                if (isset($this->categories[$annotation->annotation_category_id])) {
                    $annotation->category->color = $this->categories[$annotation->annotation_category_id]['color'];
                }
            });
        });
        return $images;
    }
    public function mount($uniqueName)
    {
        $this->uniqueName = $uniqueName;

        // Load dataset without fetching all images
        $this->dataset = Dataset::where('unique_name', $uniqueName)->first();
        $this->categories = $this->dataset->categories->mapWithKeys(function($category) {
            $category->color = $this->generateRandomRgba();
            return [$category->id => $category];
        })->toArray();
        //$this->loadMore();
    }

    public function render()
    {
        //return view('livewire.full-pages.dataset-view', ['images' => $this->dataset->images()->with(['annotations.category'])->paginate(10)]);
        return view('livewire.full-pages.dataset-view');

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
                    if (isset($this->categories[$annotation->annotation_category_id])) {
                        $annotation->category->color = $this->categories[$annotation->annotation_category_id]['color'];
                    }
                });
            });

            $this->images = collect($this->images)->concat($newImages);
            //$this->images = collect($this->images)->concat($newImages)->toArray();
            $this->imagesLoaded += $newImages->count();
            $this->newlyLoadedImages = $newImages->toArray();
        }
    }



}
