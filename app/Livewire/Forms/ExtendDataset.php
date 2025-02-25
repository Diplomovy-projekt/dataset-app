<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\ImportService\Strategies\ExtendDatasetStrategy;
use App\ImportService\Strategies\NewDatasetStrategy;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use App\Traits\DatasetImportHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ExtendDataset extends Component
{
    use WithFileUploads, DatasetImportHelper;

    public $errors;
    public $lockUpload = false;
    public $editingDataset;
    public $annotationFormats = AppConfig::ANNOTATION_FORMATS_INFO;
    public $techniques;

    # Selectable form fields
    public $selectedFormat;
    public $selectedTechnique;

    # Chunked upload
    public $chunkSize = AppConfig::UPLOAD_CHUNK_SIZE;
    public $fileChunk;
    public $displayName;
    public $uniqueName;
    public $fileSize;
    public $finalFile;
    public $validated = false;

    #[On('extend-selected')]
    public function setDatasetInfo($uniqueName)
    {
        $this->setDataset($uniqueName);
    }
    public function mount($editingDataset = null)
    {
        $this->techniques = array_values(array_map(function ($technique) {
            return [
                'key' => $technique,
                'value' => $technique,
            ];
        }, AppConfig::ANNOTATION_TECHNIQUES));
        $this->setDataset($editingDataset);
    }
    private function setDataset($uniqueName)
    {
        $this->editingDataset = $uniqueName ? Dataset::where('unique_name', $uniqueName)->first() : null;
        if($this->editingDataset){
            $this->selectedTechnique = $this->editingDataset->annotation_technique;
        }
    }
    public function render()
    {
        return view('livewire.forms.extend-dataset');
    }

    public function finishImport(ZipManager $zipManager): void
    {
        $zipExtracted = $zipManager->processZipFile($this->finalFile);

        $payload = [
            "unique_name" => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            "parent_dataset_unique_name" => $this->editingDataset->unique_name,
            'format' => $this->selectedFormat,
            'technique' => $this->editingDataset->annotation_technique,
        ];

        if (!$zipExtracted->isSuccessful()) {
            $this->errors['message'] = $zipExtracted->message;
            return;
        }

        $importService = app(ImportService::class, ['strategy' => new ExtendDatasetStrategy()]);
        $datasetImported = $importService->handleImport($payload);

        if($datasetImported->isSuccessful()){
            $this->redirectRoute('dataset.show', ['uniqueName' => $this->editingDataset->unique_name]);
        } else {
            $this->errors['data'] = $this->normalizeErrors($datasetImported->data);
            $this->errors['message'] = $datasetImported->message;
            $this->lockUpload = false;
            $this->reset($this->finalFile, $this->fileChunk, $this->displayName, $this->uniqueName, $this->fileSize);

        }
    }

    public function updatedFileChunk()
    {
        $this->validateDataset();
        $this->chunkUpload();
    }
    private function validateDataset()
    {
        if (!$this->validated) {
            Gate::authorize('update-dataset', $this->editingDataset->id);
            $rules = [
                'fileChunk' => 'required',
                'selectedFormat' => 'required',
            ];
            $this->validate($rules);
            $this->validated = true;
        }
    }
}
