<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\ImageService\ImageRendering;
use App\Models\ActionRequest;
use App\Models\Image;
use App\Utils\ImageQuery;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewReduceDataset extends Component
{
    use WithPagination, ImageRendering;
    public int $perPage = 25;
    #[Locked]
    public int $requestId;
    public $datasetId;
    public string $searchTerm = '';
    public array $stats = [];
    public array $toggleClasses = [];

    #[Computed(persist: true, seconds: 900)]
    public function paginatedImages()
    {
        $images = $this->fetchImages();
        return $this->prepareImagesForSvgRendering($images);
    }

    public function mount($requestId)
    {
        $request = ActionRequest::findOrFail($requestId);
        if($request->status != 'pending') {
            abort(403, 'Request is not pending');
        }
        $this->datasetId = $request->dataset_id;
        $this->initStats();
    }

    public function render()
    {
        return view('livewire.full-pages.review-reduce-dataset');
    }

    public function search()
    {
        $this->searchTerm = trim($this->searchTerm);
        unset($this->paginatedImages);
    }
    private function fetchImages()
    {
        $request = ActionRequest::find($this->requestId);
        $payload = $request->payload;
        $imageIds = $payload['image_ids'];
        $this->searchTerm = trim($this->searchTerm);

        return ImageQuery::forDatasets([$this->datasetId])
            ->search($this->searchTerm)
            ->includeImages($imageIds)
            ->get();
    }

    private function initStats()
    {
        $request = ActionRequest::find($this->requestId);
        $payload = $request->payload;
        $images = Image::whereIn('id', $payload['image_ids'])->with(['annotations.class'])->get();
        $this->stats['stats'] = [
            'numImages' => count($payload['image_ids']),
            'numAnnotations' => 0,
            'numClasses' => 0,
        ];
        foreach ($images as $image) {
            $this->stats['stats']['numAnnotations'] += $image->annotations->count();
            $this->stats['stats']['numClasses'] += $image->annotations->groupBy('class_id')->count();
        }
        $this->stats['image_stats'] = Util::getImageSizeStats($payload['image_ids'], true);
        $this->toggleClasses = collect($images)->pluck('annotations')->flatten(1)->pluck('class')->unique('id')->toArray();


        $datasetPath = Util::getDatasetPath($images->first()->dataset_folder);
        foreach ($this->toggleClasses as &$class) {
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class['id']))
                ->first();
            $class['image'] = [
                'dataset' => $images->first()->dataset_folder,
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class['id'],
            ];
        }
        unset($class);
    }
}
