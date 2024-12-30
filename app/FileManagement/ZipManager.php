<?php

namespace App\FileManagement;

use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ZipManager
{
    public function processZipFile($file): Response
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
        $path = Storage::disk('storage')->path(AppConfig::LIVEWIRE_TMP_PATH . $file->getFilename());

        // Open and extract the zip file
        if ($zip->open($path) === true) {
            $extractPath = Storage::disk('storage')->path(AppConfig::LIVEWIRE_TMP_PATH  . pathinfo($file->getFilename(), PATHINFO_FILENAME));

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

}
