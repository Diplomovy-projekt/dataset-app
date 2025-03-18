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
            foreach ($errors as $filename => $errorData) {
                // Check if the value is an array
                if (is_array($errorData)) {
                    // Recursive function to flatten nested arrays
                    $this->flattenErrorArray($flattenedErrors, $errorData, $filename);
                } else {
                    // In case there's a single error for the file, just add it
                    $flattenedErrors[] = ['filename' => $filename, 'error' => $errorData];
                }
            }

            return $flattenedErrors;
        }

        return [['error' => $errors]]; // Handle case when it's not an array
    }

    private function flattenErrorArray(&$result, $errors, $filename, $prefix = '')
    {
        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                // For nested arrays, recurse with updated prefix
                $newPrefix = is_numeric($key) ? $prefix : "$prefix$key: ";
                $this->flattenErrorArray($result, $value, $filename, $newPrefix);
            } else {
                // Add leaf error with full path prefix
                $result[] = [
                    'filename' => $filename,
                    'error' => $prefix . $value
                ];
            }
        }
    }

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
