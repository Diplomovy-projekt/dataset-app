<?php

namespace App\Traits;

use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\ImportService\Strategies\NewDatasetStrategy;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait DatasetImportHelper
{
    public function normalizeErrors($errors): array
    {
        if (is_array($errors)) {
            $flattenedErrors = [];

            // Iterate through each file's errors
            foreach ($errors as $filename => $errorArray) {
                // Check if the value is an array and flatten it
                if (is_array($errorArray)) {
                    foreach ($errorArray as $error) {
                        $flattenedErrors[] = ['filename' => $filename, 'error' => $error];
                    }
                } else {
                    // In case there's a single error for the file, just add it
                    $flattenedErrors[] = ['filename' => $filename, 'error' => $errorArray];
                }
            }

            return $flattenedErrors;
        }

        return [['error' => $errors]]; // Handle case when it's not an array
    }


    /*public function chunkUpload()
    {
        $chunkFileName = $this->fileChunk->getFileName();
        $finalFilePath = 'livewire-tmp/' . $this->uniqueName;

        // Read the chunk file
        $buff = Storage::disk('private')->get('livewire-tmp/' . $chunkFileName);

        // Append chunk to final file
        Storage::disk('private')->append($finalFilePath, $buff, null);

        // Delete the chunk file
        Storage::disk('private')->delete('livewire-tmp/' . $chunkFileName);

        // Check if the file is complete
        $curSize = Storage::disk('private')->size($finalFilePath);
        if ($curSize == $this->fileSize) {
            $this->finalFile = TemporaryUploadedFile::createFromLivewire('/' . $this->uniqueName);
            $this->finishImport(app(ZipManager::class));
        }
    }*/
    public function chunkUpload() {
        $chunkFileName = $this->fileChunk->getFileName();

        // Get the chunk data
        $buff = Storage::disk('private')->get('livewire-tmp/' . $chunkFileName);

        // Append chunk to final file using direct file operations instead of Storage::append
        $finalFilePath = Storage::disk('private')->path('livewire-tmp/' . $this->uniqueName);

        // Open file in append mode
        $handle = fopen($finalFilePath, 'a+');
        fwrite($handle, $buff);
        fclose($handle);

        // Delete the chunk file
        Storage::disk('private')->delete('livewire-tmp/' . $chunkFileName);

        // Check if the file is complete
        $curSize = Storage::disk('private')->size('livewire-tmp/' . $this->uniqueName);
        if ($curSize == $this->fileSize) {
            $this->finalFile = TemporaryUploadedFile::createFromLivewire('/' . $this->uniqueName);
            $this->finishImport(app(ZipManager::class));
        }
    }

}
