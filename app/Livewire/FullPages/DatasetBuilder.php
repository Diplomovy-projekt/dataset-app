<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\ImageService\ImageRendering;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Models\MetadataValue;
use App\Utils\QueryUtil;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetBuilder extends Component
{
    use ImageRendering, WithPagination;
    #[Locked]
    public $currentStage = 0;
    #[Locked]
    public $completedStages = [];
    #[Locked]
    public $stageData = [
        1 => [
            'title' => 'Select Categories',
            'description' => 'Choose the categories relevant to your dataset for this project.',
            'component' => 'builder.categories-stage',
            'method' => 'categoriesStage',
        ],
        2 => [
            'title' => 'Dataset Origin',
            'description' => 'Specify the source or origin of the dataset you want to use.',
            'component' => 'builder.origin-stage',
            'method' => 'originStage',
        ],
        3 => [
            'title' => 'Datasets Selection',
            'description' => 'Select the specific datasets required for your project.',
            'component' => 'builder.datasets-stage',
            'method' => 'datasetsStage',
        ],
        4 => [
            'title' => 'Final Selection',
            'description' => 'Select any images that you wish to EXCLUDE from your final dataset',
            'component' => 'builder.final-stage',
            'method' => 'finalStage',
        ],
        5 => [
            'title' => 'Download',
            'description' => 'Download the final prepared dataset for your project.',
            'component' => 'builder.download-stage',
            'method' => 'downloadFilter',
        ],
    ];

    #[Locked]
    public $categories = [];
    public $selectedCategories = [];
    #[Locked]
    public $metadataValues = [];
    public $selectedMetadataValues = [];
    public $skipTypes = [];
    #[Locked]
    public $datasets = [];
    public array $selectedDatasets = [
    ];
    public $selectedClasses = [];
    private $images = [];
    public $selectedImages = [];
    public $finalDataset = [
        'stats' => [],
        'metadataValues' => [],
        'categories' => [],
    ];
    public int $perPage = 25;

    #[Computed]
    public function paginatedImages()
    {
        $images = $this->imagesQuery()->paginate($this->perPage);
        return $this->prepareImagesForSvgRendering($images);
    }
    #[On('add-selected')]
    public function receiveSelected($selectedClasses, $datasetId)
    {
        //$this->selectedClasses = $this->selectedClasses + $selectedClasses;
        $this->selectedClasses[$datasetId] = $selectedClasses;

    }
    public function render()
    {
        /*$this->datasets = Dataset::with(['classes', 'metadataValues', 'categories'])
            ->orderBy('id', 'desc')
            ->limit(6) // Get only the last two
            ->get();

        foreach ($this->datasets as $dataset) {
            $dataset->stats = $dataset->getStats();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0];
            $dataset->image = $dataset->image->toArray();
        }
        $this->datasets = $this->datasets->toArray();*/
        return view('livewire.full-pages.dataset-builder');
    }

    public function updatedPerPage()
    {
        unset($this->images);
    }

    public function nextStage()
    {
        if($this->currentStage < count($this->stageData)){
            $this->completedStages[] = $this->currentStage;
            $this->currentStage++;
            $this->applyStageFilters();
        }
    }

    public function previousStage()
    {
        if($this->currentStage > 1){
            $this->currentStage--;
            $this->completedStages =array_values(array_diff($this->completedStages, [$this->currentStage]));
            $this->applyStageFilters();
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function applyStageFilters()
    {
        $stageDetails = $this->stageData[$this->currentStage] ?? null;

        if ($stageDetails && method_exists($this, $stageDetails['method'])) {
            //$this->{$stageDetails['method']}();
            $method = $stageDetails['method'];

            // Resolve method dependencies
            $reflection = new \ReflectionMethod($this, $method);
            $parameters = $reflection->getParameters();
            $dependencies = array_map(fn($param) => app($param->getType()?->getName()), $parameters);

            $this->{$method}(...$dependencies);
        }
    }

    private function categoriesStage()
    {
        $this->categories = DatasetCategory::getAllUniqueCategories();
        $this->categories = $this->categories->map(function ($category) {
            $datasetUniqueName = Dataset::whereRelation('categories', 'category_id', $category->id)->pluck('unique_name')->first();
            $image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($datasetUniqueName))[0];
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $image->toArray(),
            ];
        })->toArray();
    }

    private function originStage()
    {
        $this->metadataValues = DatasetMetadata::getGroupedMetadataByCategories($this->selectedCategories);
        $this->datasetsStage();
    }

    public function updatedSkipTypes()
    {
        $this->datasetsStage();
    }
    public function updatedSelectedMetadataValues()
    {
        $this->datasetsStage();
    }
    private function datasetsStage()
    {
        $this->datasets = [];
        // Get all explicitly selected metadata values
        $explicitlyIncluded = array_keys(array_filter($this->selectedMetadataValues));
        // Get all metadata values from skipped types (treat them as included)
        $skippedValues = MetadataValue::whereIn('metadata_type_id', $this->skipTypes)
            ->pluck('id')
            ->all();
        // Combine both sets of values
        $metadataValueIds = array_unique(array_merge($explicitlyIncluded, $skippedValues));

        // Query datasets that match at least one metadata value
        $datasetMetadataIds = DatasetMetadata::whereIn('metadata_value_id', $metadataValueIds)
            ->whereHas('dataset.categories', function ($query) {
                $query->whereIn('category_id', $this->selectedCategories);
            })
            ->distinct()
            ->pluck('dataset_id');
        // Query datasets that have no metadata but belong to selected categories
        $datasetsWithoutMetadata = Dataset::whereDoesntHave('metadataValues')
            ->whereHas('categories', function ($query) {
                $query->whereIn('category_id', $this->selectedCategories);
            })
            ->pluck('id');
        // Merge both sets of datasets
        $datasetIds = $datasetMetadataIds->merge($datasetsWithoutMetadata)->unique();

        $this->datasets = Dataset::whereIn('id', $datasetIds)
            ->with(['classes', 'metadataValues', 'categories'])
            ->get()
            ->map(function ($dataset) {
                $dataset->stats = $dataset->getStats();
                $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0]->toArray();
                return $dataset;
            })
            ->toArray();

        $this->selectedImages = [];
    }

    private function finalStage()
    {
        $this->selectedImages = [];
        unset($this->paginatedImages);
        $this->finalDataset['stats'] = $this->getCustomStats();
    }
    private function imagesQuery()
    {
        $classIds = $this->getSelectedClassesForSelectedDatasets();

        return Image::whereIn('dataset_id', array_keys(array_filter($this->selectedDatasets)))
            ->whereNotIn('id', $this->selectedImages ?? [])
            // Only include images that has annotations with selected classes
            ->whereHas('annotations.class', function ($query) use ($classIds) {
                $query->whereIn('id', $classIds);
            })
            // Include only annotations with selected classes for the images
            ->with(['annotations' => function ($query) use ($classIds) {
                $query->whereIn('annotation_class_id', $classIds);
            }, 'annotations.class']);
    }

    private function downloadFilter()
    {
        $this->images = $this->imagesQuery()->get()->toArray();

        $this->finalDataset['stats'] = $this->getCustomStats();
        $datasetIds = array_keys(array_filter($this->selectedDatasets));
        $this->finalDataset['categories'] = Category::whereIn('id', function ($query) use ($datasetIds) {
            $query->select('category_id')
                ->from('dataset_categories')
                ->whereIn('dataset_id', $datasetIds);
        })->get(['id', 'name'])->toArray();

        $this->finalDataset['metadataValues'] = MetadataValue::whereIn('id', function ($query) use ($datasetIds) {
            $query->select('metadata_value_id')
                ->from('dataset_metadata')
                ->whereIn('dataset_id', $datasetIds);
        })->get(['id', 'value'])->toArray();
    }

    public function cacheQuery()
    {
        $payload['query'] = \EloquentSerialize::serialize($this->imagesQuery());
        $payload['classIds'] = $this->getSelectedClassesForSelectedDatasets();
        $payload['selectedImages'] = $this->selectedImages;
        $payload['datasets'] = array_keys(array_filter($this->selectedDatasets));

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

    private function getSelectedClassesForSelectedDatasets(): array
    {
        $eligiblePerDataset = array_intersect_key($this->selectedClasses, array_filter($this->selectedDatasets));
        // Flatten the arrays and merge them
        $classIds = [];
        foreach($eligiblePerDataset as $datasetId => $classes) {
            $classIds = $classIds + array_filter($classes);
        }
        return array_keys($classIds);
    }

    private function getCustomStats()
    {
        $images = $this->imagesQuery()->get()->toArray();
        return [
            'numImages' => count($images),
            'numClasses' => count($this->getSelectedClassesForSelectedDatasets()),
            'numAnnotations' => array_sum(array_map(fn($image) => count($image['annotations']), $images)),
        ];
    }
}
