<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

/*#[Lazy(isolate: false)]*/
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

    #[On('edit-selected')]
    public function setDatasetInfo($uniqueName)
    {
        $this->setDataset($uniqueName);
    }
    public function mount($editingDataset = null)
    {
        $this->metadataTypes = MetadataType::with('metadataValues')->get();
        $this->selectedMetadata = $this->metadataTypes->pluck('metadataValues', 'id')->map(function () {
            return ['metadataValues' => []];
        })->toArray();
        $this->categories = Category::all()->toArray();
        if($editingDataset){
            $this->setDataset($editingDataset);
        }
    }
    private function setDataset($uniqueName)
    {
        $this->editingDataset = $uniqueName ? Dataset::where('unique_name', $uniqueName)->first() : null;
        $this->populateFormFields();
    }

    public function updateDatasetInfo()
    {
        Gate::authorize('update-dataset', $this->editingDataset->unique_name);

        try {
            $this->editingDataset->description = $this->description;
            $this->editingDataset->display_name = $this->displayName;

            $ids = array_merge(...array_column($this->selectedMetadata, 'metadataValues'));
            $this->editingDataset->metadataValues()->sync($ids);
            $this->editingDataset->categories()->sync($this->selectedCategories);
            $this->editingDataset->save();

            $this->dispatch('refresh');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', [
                'type' => 'error',
                'message' => 'Failed to update dataset. Please try again.'
            ]);
        }
    }

    private function populateFormFields()
    {
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
