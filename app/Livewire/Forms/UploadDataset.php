<?php

namespace App\Livewire\Forms;

use App\AnnotationHandler\ImportService;
use App\FileManagement\UploadService;
use App\Models\AnnotationFormat;
use App\Models\PropertyType;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    # Chunked upload
    public $chunkSize = 1000000; // 1 MB
    public $fileChunk;

    public $fileName;
    public $fileSize;
    public $finalFile;
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
    public function updatedFileChunk()
    {
        $chunkFileName = $this->fileChunk->getFileName();
        $fileChunkPath   = Storage::disk('private')->path('livewire-tmp/'.$chunkFileName);
        $realPath = $this->fileChunk->getRealPath();

        # Load chunk file into buff
        $file = fopen($fileChunkPath, 'rb');
        // TODO posledny chunk bude asi mensi ako chunkSize
        $buff = fread($file, $this->chunkSize);
        fclose($file);

        # Append chunk to final file
        $finalPath = Storage::disk('private')->path('/livewire-tmp/'.$this->fileName);
        $final = fopen($finalPath, 'ab');
        fwrite($final, $buff);
        fclose($final);
        unlink($fileChunkPath);

        # Check if file is complete
        $curSize = Storage::disk('private')->size('/livewire-tmp/'.$this->fileName);
        if( $curSize == $this->fileSize ){
            $this->finalFile =
                TemporaryUploadedFile::createFromLivewire('/'.$this->fileName);
        }
    }
}
