<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\ImageService\MyImageManager;
use App\Models\Dataset;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class DatasetIndex extends Component
{
    use ImageRendering, WithPagination, WithoutUrlPagination;
    private $datasets;
    public $searchTerm;

    #[Computed]
    public function paginatedDatasets()
    {
        return Dataset::approved()
            ->when($this->searchTerm, function ($query) {
                $query->where('display_name', 'like', '%' . $this->searchTerm . '%');
            })
            ->with([
                'images' => fn($query) => $query->limit(1)->with(['annotations.class']),
                'classes'
            ])
            ->paginate(10)
            ->through(fn($dataset) => $this->processDataset($dataset));
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-index');
    }

    private function processDataset($dataset)
    {
        if ($dataset->images->isEmpty()) {
            $dataset->thumbnail = "placeholder-image.png";
        } else {
            $dataset->setRelation('images', $this->prepareImagesForSvgRendering($dataset->images->first()));
        }
        $dataset->stats = $dataset->getStats();
        return $dataset;
    }
}
