<?php

namespace App\Livewire\Forms;

use App\ActionRequestService\ActionRequestService;
use App\Configs\AppConfig;
use App\Models\ActionRequest;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use App\Traits\LivewireActions;
use App\Utils\Util;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

/*#[Lazy(isolate: false)]*/
class EditDataset extends Component
{
    use LivewireActions;
    public $editingDataset;
    public $metadataTypes;
    public $categories;

    # Selectable form fields
    public $selectedMetadata = [];
    #[Validate('required')]
    public $selectedCategories = [];
    public $description;
    #[Validate('required')]
    public $displayName;

    #[On('edit-selected')]
    public function setDatasetInfo($uniqueName = null)
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

    public function updateDatasetInfo(ActionRequestService $actionRequestService)
    {
        Gate::authorize('update-dataset', $this->editingDataset->unique_name);
        $this->displayName = trim($this->displayName);
        $payload = $this->buildPayload();

        $result = $actionRequestService->createRequest('edit', $payload);
        $this->handleResponse($result);
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

    private function buildPayload(): array
    {
        return [
            'dataset_unique_name' => $this->editingDataset->unique_name,
            'dataset_id' => $this->editingDataset->id,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'metadata' => array_merge(...array_column($this->selectedMetadata, 'metadataValues')),
            'categories' => $this->selectedCategories,
        ];
    }

    private function hasChanges(array $payload): bool
    {
        return (
            $payload['display_name'] !== $this->editingDataset->display_name ||
            $payload['description'] !== $this->editingDataset->description ||
            array_diff($payload['metadata'], $this->editingDataset->metadataValues()->pluck('metadata_values.id')->toArray()) ||
            array_diff($payload['categories'], $this->editingDataset->categories()->pluck('categories.id')->toArray())
        );
    }

}
