<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Utils\ImageQuery;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Profile extends Component
{
    use ImageRendering, WithPagination, WithoutUrlPagination;

    #[Computed]
    public function paginatedDatasets()
    {
        return Dataset::approved()
            ->where('user_id', auth()->id())
            ->with([
                'images' => fn($query) => $query->limit(1)->with(['annotations.class']),
                'classes'
            ])
            ->paginate(10)
            ->through(fn($dataset) => $this->processDataset($dataset));
    }

    public function render()
    {
        ImageQuery::forDatasets([9])
            ->excludeImages($payload['selectedImages'] ?? [])
            ->filterByClassIds($payload['classIds'] ?? [])
            ->chunkByAnnotations(3, function ($images) {
                foreach ($images as $image) {
                    echo "Processing Image ID: {$image['id']}, Annotations: ";
                }
            });
        return view('livewire.full-pages.profile');
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
