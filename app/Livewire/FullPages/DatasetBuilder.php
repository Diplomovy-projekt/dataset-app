<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Models\MetadataValue;
use App\Utils\QueryUtil;
use App\Utils\Util;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DatasetBuilder extends Component
{
    use ImageRendering;
    #[Locked]
    public $currentStage = 0;
    #[Locked]
    public $completedStages = [];
    #[Locked]
    public $stageData = [
        1 => ['title' => 'Select Categories', 'description' => 'Choose the categories relevant to your dataset for this project.'],
        2 => ['title' => 'Dataset Origin', 'description' => 'Specify the source or origin of the dataset you want to use.'],
        3 => ['title' => 'Datasets Selection', 'description' => 'Select the specific datasets required for your project.'],
       /* 4 => ['title' => 'Classes Selection', 'description' => 'Define the classes or labels applicable to your dataset.'],*/
        4 => ['title' => 'Final Selection', 'description' => 'Review and confirm your selections before proceeding.'],
        5 => ['title' => 'Download', 'description' => 'Download the final prepared dataset for your project.'],
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
    #[Locked]
    public $classes = [];
    public $selectedClasses = [];

    public $images = [];
    public $selectedImages = [];

    #[On('add-selected')]
    public function receiveSelected($selectedClasses)
    {
        $this->selectedClasses = $this->selectedClasses + $selectedClasses;
    }
    public function render()
    {
        /*$this->datasets = Dataset::with(['classes', 'metadataValues', 'categories'])->get();
        foreach ($this->datasets as $dataset) {
            $dataset->annotationCount = $dataset->annotations()->count();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0];
            $dataset->image = $dataset->image->toArray();
        }
        $this->datasets = $this->datasets->toArray();*/
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
            case 9:
                $this->classesFilter();
                break;
            case 4:
                $this->finalSelectionFilter();
                break;
            case 5:
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
        $this->datasets = DatasetCategory::whereIn('category_id', $this->selectedCategories)->select('dataset_id as id')->get()->toArray();
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
            $dataset->annotationCount = $dataset->annotations()->count();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0]->toArray();
        }
        $this->datasets = $this->datasets->toArray();
    }

    private function classesFilter()
    {
        dd($this->selectedClasses);
        //$this->dispatch('send-to-dataset-builder');
        /*$this->datasets = $this->datasets->filter(function($dataset){
            return in_array($dataset->id, array_keys($this->selectedDatasets));
        });*/
    }
    private function finalSelectionFilter()
    {
        //dd($this->selectedDatasets, $this->selectedClasses);
        $classIds = array_keys(array_filter($this->selectedClasses, fn($value) => $value === true));
        $images = Image::whereIn('dataset_id', array_keys($this->selectedDatasets))
            ->whereHas('annotations.class', function ($query) use ($classIds) {
                $query->whereIn('id', $classIds);
            })
            ->with(['annotations' => function ($query) use ($classIds) {
                $query->whereIn('annotation_class_id', $classIds); // Filter annotations to only include selected classes
            }, 'annotations.class']) // Eager-load filtered annotations and their class
            ->get();
        $this->images = $this->prepareImagesForSvgRendering($images);
    }

    private function downloadFilter()
    {
    }

}
