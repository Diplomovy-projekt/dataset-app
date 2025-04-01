<?php

namespace App\Livewire\Forms;

use App\ActionRequestService\ActionRequestService;
use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use App\Traits\DatasetImportHelper;
use App\Traits\LivewireActions;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class DatasetUpload extends Component
{
    use WithFileUploads, DatasetImportHelper, LivewireActions;

    public $mode = 'new'; // 'new' or 'extend'
    public $errors, $lockUpload = false;
    public $annotationFormats, $techniques, $metadataTypes, $categories;
    public $selectedFormat, $selectedTechnique, $selectedMetadata = [], $selectedCategories = [], $description;

    # Chunked upload
    public $chunkSize = AppConfig::UPLOAD_CHUNK_SIZE;
    public $fileChunk, $displayName, $uniqueName, $fileSize, $finalFile, $validated = false;

    public $editingDataset;

    #[On('extend-selected')]
    public function setDatasetInfo($uniqueName)
    {
        $this->mode = 'extend';
        $this->editingDataset = Dataset::where('unique_name', $uniqueName)->first();

        if ($this->editingDataset) {
            $this->selectedTechnique = $this->editingDataset->annotation_technique;
        }
    }
    #[On('new-upload')]
    public function resetForm()
    {
        $this->selectedTechnique = null;
        $this->mode = 'new';
    }
    public function mount($mode = 'new', $editingDataset = null)
    {
        $this->mode = $mode;
        $this->annotationFormats = AppConfig::ANNOTATION_FORMATS_INFO;
        $this->techniques = array_map(fn($tech) => ['key' => $tech, 'value' => $tech], AppConfig::ANNOTATION_TECHNIQUES);
        $this->metadataTypes = MetadataType::with('metadataValues')->get();
        $this->selectedMetadata = collect($this->metadataTypes)->map(fn() => ['metadataValues' => []])->toArray();
        $this->categories = Category::all()->toArray();

        if ($mode === 'extend' && $editingDataset) {
            $this->setDataset($editingDataset);
        }
    }

    private function setDataset($uniqueName)
    {
        $this->editingDataset = Dataset::where('unique_name', $uniqueName)->first();
        if ($this->editingDataset) {
            $this->selectedTechnique = $this->editingDataset->annotation_technique;
        }
    }

    public function render()
    {
        return view('livewire.forms.dataset-upload');
    }

    public function finishImport(ZipManager $zipManager)
    {
        $zipExtracted = $zipManager->processZipFile($this->finalFile);

        $payload = [
            "display_name" => pathinfo($this->displayName, PATHINFO_FILENAME),
            "unique_name" => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $this->selectedFormat,
            'technique' => $this->mode === 'extend' ? $this->editingDataset->annotation_technique : $this->selectedTechnique,
        ];

        if ($this->mode === 'new') {
            $payload['metadata'] = array_merge(...array_column($this->selectedMetadata, 'metadataValues'));
            $payload['categories'] = $this->selectedCategories;
            $payload['description'] = $this->description;
        } else {
            $payload['parent_dataset_unique_name'] = $this->editingDataset->unique_name;
        }

        if (!$zipExtracted->isSuccessful()) {
            $this->errors['message'] = $zipExtracted->message;
            return;
        }

        $importService = app(ImportService::class);
        $result = $importService->handleImport($payload);

        if ($result->isSuccessful()) {
            $actionType = $this->mode === 'new' ? 'new' : 'extend';

            if ($this->mode === 'extend') {
                $datasetId = Dataset::where('unique_name', $payload['parent_dataset_unique_name'])->first()->id;
                $actionPayload = [
                    'dataset_id' => $datasetId,
                    'dataset_unique_name' => $payload['parent_dataset_unique_name'],
                    'child_unique_name' => $payload['unique_name']
                ];
            } else {
                $datasetId = Dataset::where('unique_name', $payload['unique_name'])->first()->id;
                $actionPayload = [
                    'dataset_id' => $datasetId,
                    'dataset_unique_name' => $payload['unique_name']
                ];
            }

            $result = app(ActionRequestService::class)->createRequest($actionType, $actionPayload);
            $this->lockUpload = false;
            $this->handleResponse($result);
        } else {
            $this->errors['data'] = $this->normalizeErrors($result->data);
            $this->errors['message'] = $result->message;
            $this->lockUpload = false;
            $this->reset('finalFile', 'fileChunk', 'displayName', 'uniqueName', 'fileSize');
        }
    }

    public function updatedFileChunk()
    {
        try {
            $this->validateDataset();
            $this->lockUpload = true;
            $this->chunkUpload();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->lockUpload = false;
            throw $e;
        }
    }

    private function validateDataset()
    {
        if (!$this->validated) {
            $this->errors = null;
            $rules = [
                'fileChunk' => 'required',
                'selectedFormat' => 'required',
            ];
            if ($this->mode === 'new') {
                $rules['selectedTechnique'] = 'required';
                $rules['selectedCategories'] = 'required';
                $rules['description'] = 'nullable|string';
                Gate::authorize('post-dataset');
            } else {
                Gate::authorize('update-dataset', $this->editingDataset->id);
            }

            $this->validate($rules);
            $this->validated = true;
        }
    }
}
