<?php

namespace App\Livewire\FullPages;

use App\ActionRequestService\ActionRequestService;
use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\ActionRequest;
use App\Models\Dataset;
use App\Models\Image;
use App\Models\Scopes\DatasetVisibilityScope;
use App\Traits\LivewireActions;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering, LivewireActions;
    #[Locked]
    public $uniqueName;
    public $dataset;
    public $searchTerm;
    public Collection $metadata;
    public Collection $categories;
    public array $toggleClasses;
    #[Locked]
    public string $modalStyle;
    public array $selectedImages = [];
    public int $perPage = 25;
    #[Locked]
    public array $request;

    #[Computed(persist: true, seconds: 900)]
    public function paginatedImages()
    {
        Util::logStart("paginatedImages");
        $images = $this->fetchImages();
        $preparedImages = $this->prepareImagesForSvgRendering($images);
        Util::logEnd("paginatedImages");
        return $preparedImages;
    }

    public function mount($uniqueName, $requestId = null)
    {
        $query = Dataset::query();
        $this->request = [
            'id' => $requestId,
            'route' => Route::currentRouteName()
        ];
         if (!isset($requestId)) {
            $query->approved();
        }
        else{
            $request = ActionRequest::where('id', $requestId)->where('status', 'pending')->first();
            if(!$request){
                abort(404);
            }
        }

        $dataset = $query->where('unique_name', $this->uniqueName)->with(['classes'])->first();
        if (!$dataset) {
            return redirect()->route('dataset.index');
        }
        $this->initProperties($dataset);
    }

    public function initProperties($dataset)
    {
        $dataset->stats = $dataset->getStats();
        $datasetPath = Util::getDatasetPath($dataset);
        foreach ($dataset->classes as $class) {
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class->id))
                ->first();
            $class->image = [
                'dataset' => $dataset->unique_name,
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class->id,
            ];
        }

        $this->dataset = $dataset->toArray();
        $this->dataset['image_stats'] = Util::getImageSizeStats([$dataset->id]);
        $this->toggleClasses = $dataset['classes']->toArray();
        $this->metadata = $dataset->metadataGroupedByType();
        $this->categories = $dataset->categories()->get();
    }

    public function updatedPerPage()
    {
        unset($this->paginatedImages);
    }

    public function updatedSearchTerm()
    {
        $this->searchTerm = trim($this->searchTerm);
        unset($this->paginatedImages);
    }

    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('dataset_id', $this->dataset['id'])->where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            return Image::where('dataset_id', $this->dataset['id'])->with(['annotations.class'])->paginate($this->perPage);
        }
    }

    public function deleteDataset(DatasetActions $datasetService): void
    {
        $payload = ['dataset_unique_name' => $this->uniqueName,
                    'dataset_id' => Dataset::where('unique_name', $this->uniqueName)->first()->id
            ];
        $result = app(ActionRequestService::class)->createRequest('delete', $payload);
        $this->handleResponse($result);
    }

    public function deleteImages(DatasetActions $datasetService)
    {
        $payload = ['dataset_unique_name' => $this->uniqueName,
            'dataset_id' => Dataset::where('unique_name', $this->uniqueName)->first()->id,
            'image_ids' => $this->selectedImages
        ];
        $result = app(ActionRequestService::class)->createRequest('reduce', $payload);
        $this->handleResponse($result);
    }

    public function cacheQuery($id)
    {
        $query = Image::where('dataset_id', $id)->with('annotations.class');
        $payload['query'] = \EloquentSerialize::serialize($query);
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));
        $this->dispatch('store-download-token', token: $token);
    }

}
