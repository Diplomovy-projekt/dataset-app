<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use App\Models\DatasetProperty;
use App\Models\PropertyType;
use App\Models\PropertyValue;
use Livewire\Component;

class DatasetBuilder extends Component
{
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

    public $originData = [];
    public $selectedOriginData = [];

    public $datasets = [];
    public $selectedDatasets = [];

    public $classes = [];
    public $selectedClasses = [];

    public $finalSelection = [];
    public $selectedFinalSelection = [];

    public function render()
    {
        $datasets = Dataset::with(['classes', 'datasetProperties.propertyValue'])->get();

        $this->datasets = $datasets;
        $this->currentStage = 3;
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
                $this->originDataFilter();
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
        $categoryPropertyTypeIds = PropertyType::where('name', 'Category')->pluck('id');

        $propertyValues = PropertyValue::whereIn('property_type_id', $categoryPropertyTypeIds)
            ->whereIn('id', DatasetProperty::pluck('property_value_id'))
            ->distinct()
            ->get();
        $this->categories = $propertyValues;
    }

    private function originDataFilter()
    {
        // 1. Get the ID of the Category property type
        $categoryPropertyId = PropertyType::where('name', 'Category')
            ->value('id');

        // 2. Get dataset IDs that match selected categories
        $datasetIds = DatasetProperty::where('property_value_id', $this->selectedCategories)
            ->pluck('dataset_id')
            ->toArray();

        // 3. Get all property values for these datasets
        $datasetProperties = DatasetProperty::whereIn('dataset_id', $datasetIds)
            ->get()
            ->toArray();

        // 4. Get available property values (excluding categories)
        $availableValues = PropertyValue::whereIn('property_type_id', array_column($datasetProperties, 'property_value_id'))
            ->whereNot('property_type_id', $categoryPropertyId)
            ->get()
            ->toArray();

        // 5. Get and index property types for easy lookup
        $availableTypes = PropertyType::whereIn('id', array_column($availableValues, 'property_type_id'))
            ->get()
            ->toArray();

        $typesByIds = collect($availableTypes)->keyBy('id')->toArray();

        // 6. Build the final data structure
        $this->originData = [];
        foreach ($availableValues as $value) {
            $typeId = $value['property_type_id'];

            if (!isset($this->originData[$typeId])) {
                $this->originData[$typeId] = [
                    'type' => $typesByIds[$typeId],
                    'values' => []
                ];
            }

            $this->originData[$typeId]['values'][] = $value;
        }
    }


    private function datasetsFilter()
    {
        $datasetIds = DatasetProperty::whereIn('property_value_id', $this->selectedOriginData)->pluck('dataset_id');
        $datasets = Dataset::whereIn('id', $datasetIds)->with(['classes', 'datasetProperties.propertyValue'])->get();

        $this->datasets = $datasets;
    }

    private function classesFilter()
    {
        dd($this->selectedDatasets);
    }

    private function finalSelectionFilter()
    {
    }

    private function downloadFilter()
    {
    }

}
