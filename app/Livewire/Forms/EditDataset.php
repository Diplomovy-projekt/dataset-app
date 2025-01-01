<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditDataset extends Component
{
    public $editingDataset;
    public $metadataTypes;
    public $categories;

    # Selectable form fields
    public $selectedMetadata = [];
    #[Validate('required')]
    public $selectedCategories = [];
    public $description;
    public $displayName;

    public function mount($editingDataset)
    {
        $this->editingDataset = $editingDataset;
        $this->metadataTypes = MetadataType::with('metadataValues')->get();
        $this->selectedMetadata = $this->metadataTypes->pluck('metadataValues', 'id')->map(function () {
            return ['metadataValues' => []];
        })->toArray();
        $this->categories = Category::all()->toArray();
        $this->populateFormFields();
    }
    public function render()
    {
        return view('livewire.forms.edit-dataset');
    }

    public function updateDatasetInfo()
    {
        $this->editingDataset->description = $this->description;
        $this->editingDataset->display_name = $this->displayName;
        $ids = array_merge(...array_column($this->selectedMetadata, 'metadataValues'));
        $this->editingDataset->metadataValues()->sync($ids);
        $this->editingDataset->categories()->sync($this->selectedCategories);
        $this->editingDataset->save();
    }
    private function populateFormFields()
    {
        $this->editingDataset = Dataset::where('unique_name', $this->editingDataset)->with(['categories'])->first();
        $datasetMetadata = $this->editingDataset->metadataGroupedByType()->toArray();
        array_walk($this->selectedMetadata, function (&$metadata, $id) use ($datasetMetadata) {
            if (isset($datasetMetadata[$id])) {
                $metadata['metadataValues'] = array_column($datasetMetadata[$id]['metadataValues'], 'id');
            }
        });
        $this->selectedCategories = $this->editingDataset->categories->pluck('id')->toArray();
        $this->description = $this->editingDataset->description;
        $this->displayName = $this->editingDataset->display_name;
    }
}
