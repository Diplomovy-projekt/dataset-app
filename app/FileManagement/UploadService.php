<?php

namespace App\FileManagement;

use App\AnnotationHandler\AnnotationConverterService;
use App\AnnotationHandler\Factory\AnnotationConverterFactory;
use App\AnnotationHandler\ImportService;
use App\Utils\Response;
use ZipArchive;

class UploadService
{
    public function handleUpload($file): Response
    {
        if (!$file->getClientOriginalExtension() == 'zip') {
            return Response::error('Invalid file format. Please upload a zip file.');
        }
        try {
            // Step 1: Extract the zip file
            $extractionResult = $this->extractZipFile($file);
            if (!$extractionResult) {
                return Response::error('Extraction failed', $extractionResult);
            }

            return Response::success('Extraction successful');
        } catch (\Exception $e) {
            return Response::error('An unexpected error occurred during zip extraction: ' . $e->getMessage());
        }
    }

    private function extractZipFile($file): bool
    {
        $zip = new ZipArchive;
        $path = storage_path('app/private/livewire-tmp/' . $file->getFilename());

        // Open and extract the zip file
        if ($zip->open($path) === true) {
            $extractPath = storage_path("app/private/" . pathinfo($file->getFilename(), PATHINFO_FILENAME));

            // Create the extraction directory if it doesn't exist
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0777, true);  // Ensure the directory is created
            }
            $zip->extractTo($extractPath);
            $zip->close();

            return true;
        }

        return false;
    }

    private function deleteAllTemporaryFiles(string $archiveDir)
    {
        //delete all temp files

    }

}
