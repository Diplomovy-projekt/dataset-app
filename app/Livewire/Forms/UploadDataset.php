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
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UploadDataset extends Component
{
    use WithFileUploads, DatasetImportHelper;

    public $modalStyle;
    public $errors;
    public $lockUpload = false;
    public $annotationFormats;
    public $techniques;
    public $metadataTypes;
    public $categories;

    # Selectable form fields
    public $selectedFormat;
    public $selectedTechnique;
    public $selectedMetadata = [];
    public $selectedCategories = [];
    public $description;

    # Chunked upload
    public $chunkSize = AppConfig::UPLOAD_CHUNK_SIZE;
    public $fileChunk;
    public $displayName;
    public $uniqueName;
    public $fileSize;
    public $finalFile;
    public $validated = false;


    public function mount()
    {
        $this->annotationFormats = AppConfig::ANNOTATION_FORMATS_INFO;
        $this->techniques = array_map(fn($technique) => ['key' => $technique, 'value' => $technique], AppConfig::ANNOTATION_TECHNIQUES);
        $this->metadataTypes = MetadataType::with('metadataValues')->get();
        $this->selectedMetadata = $this->metadataTypes->pluck('metadataValues', 'id')->map(function () {
            return ['metadataValues' => []];
        })->toArray();
        $this->categories = Category::all()->toArray();
    }
    public function render()
    {
        return view('livewire.forms.upload-dataset');
    }

    public function finishImport(ZipManager $zipManager)
    {
        $zipExtracted = $zipManager->processZipFile($this->finalFile);
        $payload = [
            "display_name" => pathinfo($this->displayName, PATHINFO_FILENAME),
            "unique_name" => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $this->selectedFormat,
            'metadata' => array_merge(...array_column($this->selectedMetadata, 'metadataValues')),
            'technique' => $this->selectedTechnique,
            'categories' => $this->selectedCategories,
            'description' => $this->description,
        ];

        if (!$zipExtracted->isSuccessful()) {
            $this->errors['message'] = $zipExtracted->message;
            return;
        }

        $importService = app(ImportService::class, ['strategy' => new NewDatasetStrategy()]);
        $datasetImported = $importService->handleImport($payload);

        if($datasetImported->isSuccessful()){
            $this->redirectRoute('dataset.show', ['uniqueName' => pathinfo($this->uniqueName, PATHINFO_FILENAME)]);
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
        $this->lockUpload = true;
        $this->chunkUpload();
    }
    private function validateDataset()
    {
        if (!$this->validated) {
            Gate::authorize('post-dataset');
            $rules = [
                'fileChunk' => 'required',
                'selectedFormat' => 'required',
                'selectedTechnique' => 'required',
                'selectedCategories' => 'required',
                'description' => 'nullable|string',
            ];
            $this->validate($rules);
            $this->validated = true;
        }
    }

}
