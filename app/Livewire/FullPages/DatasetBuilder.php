<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\MetadataType;
use App\Models\MetadataValue;
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
    }

    private function metadataValuesFilter()
    {
        $this->metadataValues = DatasetMetadata::getGroupedMetadataByCategories($this->selectedCategories);
    }

    private function datasetsFilter()
    {
        $query = Dataset::with(['classes', 'datasetMetadata.metadataValue']);
        $this->selectedMetadataValues = array_map(function($el){
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

        //TODO vyber take datasety iba, co splnaju vsetky include a ziadne exclude, nie len jedno, Preo tam je to havingRaw.
        //TODO na frontente automaticky zaskrtni nieco? Lebo aktualne ak nic nezaskrtnut a neskipnu tak mozno bude problem

        // If there are any valid selected metadata values, filter datasets by them
        if ($selectedMetadataValues->isNotEmpty()) {
            $datasetIds = DatasetMetadata::whereIn('metadata_value_id', $selectedMetadataValues)
                ->groupBy('dataset_id')
                ->havingRaw('COUNT(DISTINCT metadata_value_id) = ?', [$selectedMetadataValues->count()])
                ->pluck('dataset_id');

            // Only apply the filter if valid dataset IDs were found
            if ($datasetIds->isNotEmpty()) {
                $query->whereIn('id', $datasetIds);
            }
        }

        // Apply category filter if categories are selected
        if (!empty($this->selectedCategories)) {
            $query->whereIn('category_id', $this->selectedCategories);
        }

        // Execute the query and assign the results to the datasets property
        $this->datasets = $query->get();
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
