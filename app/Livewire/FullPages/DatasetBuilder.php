<?php

namespace App\Livewire\FullPages;

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
    }

    private function datasetsFilter()
    {
    }

    private function classesFilter()
    {
    }

    private function finalSelectionFilter()
    {
    }

    private function downloadFilter()
    {
    }

}
