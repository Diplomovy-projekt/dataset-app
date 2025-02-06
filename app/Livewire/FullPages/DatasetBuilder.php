<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Models\MetadataValue;
use App\Utils\QueryUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetBuilder extends Component
{
    use ImageRendering, WithPagination;
    #[Locked]
    public $currentStage = 3;
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
    public $selectedDatasets = [];
    public $selectedClasses = [];
    private $images = [];
    public $selectedImages = [];
    #[Validate('required')]
    public $exportFormat = '';
    public $availableFormats = [];
    public $finalDataset = [];
    public $failedDownload = [];
    #[Computed]
    public function paginatedImages()
    {
        $images = $this->imagesQuery()->paginate(AppConfig::PER_PAGE);
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
        //$this->datasets = Dataset::with(['classes', 'metadataValues', 'categories'])->get();
        $this->datasets = Dataset::with(['classes', 'metadataValues', 'categories'])
            ->orderBy('id', 'desc')
            ->limit(6) // Get only the last two
            ->get();
        foreach ($this->datasets as $dataset) {
            $dataset->stats = $dataset->getStats();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0];
            $dataset->image = $dataset->image->toArray();
        }
        $this->datasets = $this->datasets->toArray();
        $this->availableFormats = AppConfig::ANNOTATION_FORMATS_INFO;
        return view('livewire.full-pages.dataset-builder');
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
        // Get all selected metadata values except for skipped types
        $selectedMetadataValues = collect($this->selectedMetadataValues)
            ->filter(function ($selected, $valueId) {
                $value = MetadataValue::find($valueId);
                $typeId = $value->metadataType->id;

                // Only include if type is not skipped and value is selected
                return !in_array($typeId, $this->skipTypes);
            })
            ->map(fn($encodedValue) => json_decode($encodedValue));;

        // Separate metadata values into include and exclude groups
        $groupedMetadata = [
            'include' => $selectedMetadataValues->filter()->keys()->all(),
            'exclude' => $selectedMetadataValues->reject()->keys()->all(),
        ];

        // Include datasets with all `include` metadata IDs
        $query = DatasetMetadata::query();
        if (!empty($groupedMetadata['include'])) {
            $query->whereIn('metadata_value_id', $groupedMetadata['include'])
                ->select('dataset_id')
                ->groupBy('dataset_id')
                ->havingRaw('COUNT(DISTINCT metadata_value_id) = ?', [count($groupedMetadata['include'])]);
        }

        if (!empty($groupedMetadata['exclude'])) {
            $query->whereNotIn('dataset_id', function ($subquery) use ($groupedMetadata) {
                $subquery->select('dataset_id')
                    ->from('dataset_metadata')
                    ->whereIn('metadata_value_id', $groupedMetadata['exclude']);
            });
        }
        $datasetMetadataIds = $query->pluck('dataset_id');
        $datasetCategoryIds = DatasetCategory::whereIn('category_id', $this->selectedCategories)->pluck('dataset_id as id');
        $matchingDatasetIds = $query->getQuery()->wheres ? $datasetMetadataIds->intersect($datasetCategoryIds) : $datasetCategoryIds;

        $this->datasets = Dataset::whereIn('id', $matchingDatasetIds)->with(['classes', 'metadataValues', 'categories'])->get();

        foreach ($this->datasets as $dataset) {
            $dataset->stats = $dataset->getStats();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0]->toArray();
        }
        $this->datasets = $this->datasets->toArray();
        $this->selectedImages = [];
    }

    private function finalStage()
    {
        //$this->images = $this->imagesQuery()->get()->toArray();
        $ad = 5;
    }
    private function imagesQuery()
    {
        $classIds = $this->getSelectedClassesForSelectedDatasets();

        return Image::whereIn('dataset_id', array_keys($this->selectedDatasets))
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

    private function downloadFilter(ExportService $exportService)
    {
        $this->availableFormats = AppConfig::ANNOTATION_FORMATS_INFO;
        $this->images = $this->imagesQuery()->get()->toArray();

        $this->finalDataset['stats'] = [
            'numImages' => count($this->images),
            'numClasses' => count($this->getSelectedClassesForSelectedDatasets()),
            'numAnnotations' => array_sum(array_map(fn($image) => count($image['annotations']), $this->images)),
        ];
    }

    public function downloadCustomDataset(DatasetActions $datasetActions)
    {
        $this->validate();
        $this->images = $this->imagesQuery()->get()->toArray();

        // Export the dataset
        $response = $datasetActions->downloadDataset($this->images, $this->exportFormat);
        if(!$response->isSuccessful()) {
            $this->failedDownload['message'] = $response->getMessage();
            $this->failedDownload['data'] = $response->getData();
        }

    }

    private function getSelectedClassesForSelectedDatasets(): array
    {
        $eligiblePerDataset = array_intersect_key($this->selectedClasses, $this->selectedDatasets);
        // Flatten the arrays and merge them
        $classIds = [];
        foreach($eligiblePerDataset as $datasetId => $classes) {
            $classIds = $classIds + array_filter($classes);
        }
        return array_keys($classIds);
    }
}
