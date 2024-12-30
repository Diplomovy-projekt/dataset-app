<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\ImportService\Strategies\NewDatasetStrategy;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\MetadataType;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UploadDataset extends Component
{
    use WithFileUploads;

    public $modalStyle;
    public $errors;
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
    public $chunkSize = 20000000; // 20 MB
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

    public function finishImport()
    {
        $zipExtraction = app(ZipManager::class);
        $importService = app(ImportService::class, ['strategy' => new NewDatasetStrategy()]);
        $zipExtracted = $zipExtraction->processZipFile($this->finalFile);

        $payload = [
            "display_name" => pathinfo($this->displayName, PATHINFO_FILENAME),
            "unique_name" => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $this->selectedFormat,
            'metadata' => $this->selectedMetadata,
            'technique' => $this->selectedTechnique,
            'categories' => $this->selectedCategories,
            'description' => $this->description,
        ];

        if ($zipExtracted->isSuccessful()) {
            $datasetImported = $importService->handleImport($payload);
        }

        if(!$datasetImported->isSuccessful()) {
            $this->errors['data'] = $this->normalizeErrors($datasetImported->data);
            $this->errors['message'] = $datasetImported->message;
        }

        if($datasetImported->isSuccessful()){
            $this->redirectRoute('dataset.show', ['uniqueName' => pathinfo($this->uniqueName, PATHINFO_FILENAME)]);
        }
    }

    public function updatedFileChunk()
    {
        $this->validateDataset();
        $chunkFileName = $this->fileChunk->getFileName();

        // Read the chunk file
        $buff = Storage::disk('private')->get('livewire-tmp/' . $chunkFileName);

        // Append chunk to final file
        $finalFilePath = 'livewire-tmp/' . $this->uniqueName;
        Storage::disk('private')->append($finalFilePath, $buff, null);

        // Delete the chunk file
        Storage::disk('private')->delete('livewire-tmp/' . $chunkFileName);

        // Check if the file is complete
        $curSize = Storage::disk('private')->size($finalFilePath);
        if ($curSize == $this->fileSize) {
            $this->finalFile = TemporaryUploadedFile::createFromLivewire('/' . $this->uniqueName);
            $this->finishImport();
        }
    }
    private function validateDataset()
    {
        if (!$this->validated) {
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
    private function normalizeErrors($errors): array
    {
        if (is_array($errors)) {
            return collect($errors)
                ->flatten()
                ->toArray(); // Converts nested arrays to a flat array
        }

        return [$errors]; // If a single error message is returned
    }

}
