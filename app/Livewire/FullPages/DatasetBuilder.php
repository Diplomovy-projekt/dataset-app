<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use App\Utils\QueryUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use MongoDB\Driver\Query;

class DatasetBuilder extends Component
{
    use ImageRendering;
    public $currentStage = 0;
    public $completedStages = [];
    public $stageData = [
        1 => ['title' => 'Select Categories', 'description' => 'Choose the categories relevant to your dataset for this project.'],
        2 => ['title' => 'Dataset Origin', 'description' => 'Specify the source or origin of the dataset you want to use.'],
        3 => ['title' => 'Datasets Selection', 'description' => 'Select the specific datasets required for your project.'],
        4 => ['title' => 'Classes Selection', 'description' => 'Define the classes or labels applicable to your dataset.'],
        5 => ['title' => 'Final Selection', 'description' => 'Review and confirm your selections before proceeding.'],
        6 => ['title' => 'Download', 'description' => 'Download the final prepared dataset for your project.'],
    ];

    public $categories = [];
    public $selectedCategories = [];

    public $metadataValues = [];
    public $selectedMetadataValues = [];
    public $skipTypes = [];

    public $datasets = [];
    public $selectedDatasets = [];

    public $classes = [];
    public $selectedClasses = [];

    public $finalSelection = [];
    public $selectedFinalSelection = [];

    public function render()
    {
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

    private function applyStageFilters()
    {
        switch ($this->currentStage){
            case 1:
                $this->categoriesFilter();
                break;
            case 2:
                $this->metadataValuesFilter();
                break;
            case 3:
                $this->datasetsFilter();
                break;
            case 4:
                $this->classesFilter();
                break;
            case 5:
                $this->finalSelectionFilter();
                break;
            case 6:
                $this->downloadFilter();
                break;
        }
    }

    private function categoriesFilter()
    {
        $this->categories = DatasetCategory::getAllUniqueCategories();
        $this->categories = $this->categories->map(function ($category) {
            $datasetUniqueName = Dataset::whereRelation('categories', 'category_id', $category->id)->pluck('unique_name')->first();
            $image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($datasetUniqueName))[0];
            $image->imagePath = $image ? Util::constructPublicImgPath($datasetUniqueName, $image->filename) : AppConfig::PLACEHOLDER_IMG;
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $image->toArray(),
            ];
        })->toArray();
    }

    private function metadataValuesFilter()
    {
        $this->metadataValues = DatasetMetadata::getGroupedMetadataByCategories($this->selectedCategories);
    }

    public function updatedSkipTypes()
    {
        $this->datasetsFilter();
    }
    public function updatedSelectedMetadataValues()
    {
        $this->datasetsFilter();
    }
    private function datasetsFilter()
    {
        $selectedMetadataValues = array_map(function($el){
            return json_decode($el);
        }, $this->selectedMetadataValues);


        // Get all selected metadata values except for skipped types
        $selectedMetadataValues = collect($this->selectedMetadataValues)
            ->filter(function ($selected, $valueId) {
                // Get metadata value and its type
                $value = MetadataValue::find($valueId);
                $typeId = $value->metadataType->id;

                // Only include if type is not skipped and value is selected
                return !in_array($typeId, $this->skipTypes);
            });

        $groupedMetadata = [
            'include' => [],
            'exclude' => [],
        ];
        // Separate metadata values into include and exclude groups
        foreach($selectedMetadataValues as $valueId => $include) {
            if ($include) {
                $groupedMetadata['include'][] = $valueId;
            } else {
                $groupedMetadata['exclude'][] = $valueId;
            }
        }

        $query = DatasetMetadata::query();
        // Include datasets with all `include` metadata IDs
        if (!empty($groupedMetadata['include'])) {
            $includeIds = $groupedMetadata['include'];
            $requiredCount = count($includeIds);

            $query->whereIn('metadata_value_id', $includeIds)
                ->select('dataset_id')
                ->groupBy('dataset_id')
                ->havingRaw('COUNT(DISTINCT metadata_value_id) = ?', [$requiredCount]);
        }

        // Exclude datasets with any `exclude` metadata IDs
        if (!empty($groupedMetadata['exclude'])) {
            $excludeIds = $groupedMetadata['exclude'];

            $query->whereNotIn('dataset_id', function ($subquery) use ($excludeIds) {
                $subquery->select('dataset_id')
                    ->from('dataset_metadata')
                    ->whereIn('metadata_value_id', $excludeIds);
            });
        }

        // Apply selected categories filter from previous step
        $query->whereIn('dataset_id', function ($subquery) {
            $subquery->select('dataset_id')
                ->from('dataset_categories')
                ->whereIn('category_id', $this->selectedCategories);
        });

        $matchingDatasetIds = $query->pluck('dataset_id');
        $this->datasets = Dataset::whereIn('id', $matchingDatasetIds)->with(['classes', 'metadataValues'])->get();
        foreach ($this->datasets as $dataset) {
            $dataset->annotationCount = $dataset->annotations()->count();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0];
            $dataset->image->path = $dataset->image ? Util::constructPublicImgPath($dataset->unique_name, $dataset->image->filename) : AppConfig::PLACEHOLDER_IMG;
        }
        $this->datasets = $this->datasets->toArray();
    }

    private function classesFilter()
    {
        $this->datasets = $this->datasets->filter(function($dataset){
            return in_array($dataset->id, array_keys($this->selectedDatasets));
        });
    }

    private function finalSelectionFilter()
    {
    }

    private function downloadFilter()
    {
    }

}
