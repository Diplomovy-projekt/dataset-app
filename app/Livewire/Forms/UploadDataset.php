<?php

namespace App\Livewire\Forms;

use App\AnnotationHandler\ImportService;
use App\FileManagement\UploadService;
use App\Models\AnnotationFormat;
use App\Models\PropertyType;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadDataset extends Component
{
    use WithFileUploads;
    public $annotationFormats;
    public $propertyTypes;

    # Selectable form fields
    public $datasetFile;
    public $checkedProperties = [];
    public $selectedFormat;
    public $annotationTechnique;
    public $description;
    public function render()
    {
        $this->annotationFormats = AnnotationFormat::all();
        $this->propertyTypes = PropertyType::with('propertyValues')->get();
        return view('livewire.forms.upload-dataset');
    }

    public function submitUploadDataset(UploadService $uploadService, ImportService $importService)
    {
        $this->validate([
            'datasetFile' => 'required|file|mimes:zip',
            'selectedFormat' => 'required',
        ]);

        $response = $uploadService->handleUpload($this->datasetFile);
        $payload = [
            'file' => $this->datasetFile,
            "display_name" => pathinfo($this->datasetFile->getClientOriginalName(), PATHINFO_FILENAME),
            "unique_name" => pathinfo($this->datasetFile->getFilename(), PATHINFO_FILENAME),
            'description' => $this->description,
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
}
