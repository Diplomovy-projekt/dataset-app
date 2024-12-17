<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use App\Models\Image;
use App\Utils\AppConstants;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination;
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
        //Preprocess annotations
        $images->each(function ($image) {
            $image->annotations->each(function ($annotation) {
                // Assign color to category
                $annotation->category->color = $this->categories[$annotation->annotation_category_id]['color'];

                $annotation = $this->rescaleBbox($annotation);
                if($annotation->segmentation){
                    $annotation->segmentation = $this->rescalePolygonPoints($annotation->segmentation);
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

    private function rescalePolygonPoints($segmentation): string
    {
        $segmentation = json_decode($segmentation);
        $svgDim = AppConstants::IMG_THUMB_DIMENSIONS;
        $pointsString = '';
        foreach ($segmentation as $point) {
            $pointsString .= ($point * $svgDim) . ',';
        }
        //dd($pointsString);
        return rtrim($pointsString, ',');
    }

    private function rescaleBbox($annotation)
    {
        $thumbWidth = AppConstants::IMG_THUMB_DIMENSIONS;

        $pixelWidth = $annotation->width * $thumbWidth;
        $pixelHeight = $annotation->height * $thumbWidth;

        $annotation->x = ($annotation->center_x * $thumbWidth) - ($pixelWidth / 2);
        $annotation->y = ($annotation->center_y * $thumbWidth) - ($pixelHeight / 2);

        $annotation->width = $pixelWidth;
        $annotation->height = $pixelHeight;
        return $annotation;
    }

    private function generateRandomRgba()
    {
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);

        return [
            'fill' => "rgba($r, $g, $b, 0.2)",
            'stroke' => "rgba($r, $g, $b, 1)"
        ];
    }

    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('img_filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.category'])->paginate($this->perPage);
        }
        else {
            return $this->dataset->images()->with(['annotations.category'])->paginate($this->perPage);
        }
    }


}
