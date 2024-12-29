<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\Models\Category;
use App\Models\MetadataType;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ExtendDataset extends Component
{
    use WithFileUploads;

    public $editingDataset;
    public $annotationFormats;
    public $techniques;

    # Selectable form fields
    public $selectedFormat;
    public $selectedTechnique;

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
        $this->techniques = array_values(array_map(function ($technique) {
            return [
                'key' => $technique,
                'value' => $technique,
            ];
        }, AppConfig::ANNOTATION_TECHNIQUES));
    }
    public function render()
    {
        return view('livewire.forms.extend-dataset');
    }

    public function finishImport()
    {
        $zipExtraction = app(ZipManager::class);
        $importService = app(ImportService::class);
        $zipExtracted = $zipExtraction->processZipFile($this->finalFile);

        $uniqueName = $this->editingDataset ? $this->editingDataset->unique_name : pathinfo($this->uniqueName, PATHINFO_FILENAME);
        $payload = [
            'isEditing' => $this->editingDataset->id ?? null,
            'file' => $this->finalFile,
            "display_name" => pathinfo($this->displayName, PATHINFO_FILENAME),
            "unique_name" => $uniqueName,
            'format' => $this->selectedFormat,
            'metadata' => $this->selectedMetadata,
            'technique' => $this->selectedTechnique,
            'categories' => $this->selectedCategories,
            'description' => $this->description,
        ];

        if ($zipExtracted->isSuccessful()) {
            $datasetImported = $importService->handleImport($payload);
        }

        $this->dispatch('flash-message', [
            'success' => $datasetImported->isSuccessful(),
            'message' => $datasetImported->message
        ]);

        $this->reset([
            'fileChunk',
            'finalFile',
            'selectedFormat',
            'selectedTechnique',
            'selectedCategories',
            'selectedMetadata',
            'description'
        ]);

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

            if ($this->editingDataset) {
                unset($rules['fileChunk']);
            }
            $this->validate($rules);
            $this->validated = true;
        }
    }
}
