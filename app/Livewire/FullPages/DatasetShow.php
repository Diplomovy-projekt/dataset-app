<?php

namespace App\Livewire\FullPages;

use App\ActionRequestService\ActionRequestService;
use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ImageService\ImageRendering;
use App\Models\ActionRequest;
use App\Models\Dataset;
use App\Models\Image;
use App\Traits\LivewireActions;
use App\Utils\ImageQuery;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering, LivewireActions, WithoutUrlPagination;
    #[Locked]
    public $uniqueName;
    public $dataset;
    public string $searchTerm = '';
    public Collection $metadata;
    public Collection $categories;
    public array $toggleClasses;
    #[Locked]
    public string $modalStyle;
    public array $selectedImages = [];
    public int $perPage = 50;
    #[Locked]
    public array $request;

    #[Computed]
    public function paginatedImages()
    {
        Util::logStart("paginatedImages");
        $images = ImageQuery::forDatasets([$this->dataset['id']])
            ->search($this->searchTerm)
            ->perPage($this->perPage)
            ->get();
        $images = $this->prepareImagesForSvgRendering($images);
        Util::logEnd("paginatedImages");
        return $images;
    }


    public function mount($uniqueName, $requestId = null)
    {
        $this->resetPage();
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

    public function render()
    {
        Util::logStart("Livewire render");
        Util::logEnd("Livewire render");
        return view('livewire.full-pages.dataset-show');
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
        unset($this->paginatedImages);
    }

    #[Renderless]
    public function deleteDataset(DatasetActions $datasetService): void
    {
        $payload = ['dataset_unique_name' => $this->uniqueName,
                    'dataset_id' => Dataset::where('unique_name', $this->uniqueName)->first()->id
            ];
        $result = app(ActionRequestService::class)->createRequest('delete', $payload);
        $this->handleResponse($result);
    }

    #[Renderless]
    public function deleteImages(DatasetActions $datasetService)
    {
        $payload = ['dataset_unique_name' => $this->uniqueName,
            'dataset_id' => Dataset::where('unique_name', $this->uniqueName)->first()->id,
            'image_ids' => $this->selectedImages
        ];
        $result = app(ActionRequestService::class)->createRequest('reduce', $payload);
        $this->handleResponse($result);
    }

    #[Renderless]
    public function cacheQuery($id)
    {
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));
        $this->dispatch('store-download-token', token: $token);
    }

}
