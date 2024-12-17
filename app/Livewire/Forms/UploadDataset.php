<?php

namespace App\Livewire\Forms;

use App\AnnotationHandler\ImportService;
use App\FileManagement\ZipManager;
use App\Models\AnnotationFormat;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UploadDataset extends Component
{
    use WithFileUploads;
    public $annotationFormats;
    public $propertyTypes;

    # Selectable form fields
    public $checkedProperties = [];
    public $selectedFormat;
    public $annotationTechnique;
    public $description;

    # Chunked upload
    public $chunkSize = 20000000; // 20 MB
    public $fileChunk;
    public $displayName;
    public $uniqueName;
    public $fileSize;
    public $finalFile;
    public $validated = false;
    public function render()
    {
        $this->annotationFormats = AnnotationFormat::all();
        $this->propertyTypes = PropertyType::with('propertyValues')->get();
        return view('livewire.forms.upload-dataset');
    }

    public function finishImport()
    {
        $zipExtraction = app(ZipManager::class);
        $importService = app(ImportService::class);
        $response = $zipExtraction->processZipFile($this->finalFile);
        $payload = [
            'file' => $this->finalFile,
            "display_name" => pathinfo($this->displayName, PATHINFO_FILENAME),
            "unique_name" => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $this->selectedFormat,
            'properties' => $this->checkedProperties,
            'technique' => $this->annotationTechnique,
        ];

        if ($response->isSuccessful()) {
            $response = $importService->handleImport($payload);
        }

        $this->dispatch('flash-message', [
            'success' => $response->isSuccessful(),
            'message' => $response->message
        ]);
    }

    public function updatedFileChunk()
    {
        $validatedData = $this->validate([
            'selectedFormat' => 'required',  // Example validation rule
            'annotationTechnique' => 'required',
            'checkedProperties' => 'required',
            'description' => 'nullable|string',
        ]);
        $this->validated = true;
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
}
