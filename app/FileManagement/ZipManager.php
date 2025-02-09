<?php

namespace App\FileManagement;

use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipManager
{
    public  function processZipFile($file): Response
    {
        if (!$file->getClientOriginalExtension() == 'zip') {
            return Response::error('Invalid file format. Please upload a zip file.');
        }
        try {
            // Step 1: Extract the zip file
            $extractionResult = $this->extractZipFile($file);
            if (!$extractionResult) {
                return Response::error('An error occurred while extracting the zip file.');
            }

            return Response::success();
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

    /**
     * @throws \Exception
     */
    public static function createZipFromFolder($folderPath)
    {
        // Initialize ZipArchive object
        $zip = new ZipArchive();
        $zipPath = $folderPath . '.zip';
        // Open the ZIP file for writing
        if ($zip->open($zipPath , ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Failed to create ZIP file');
        }

        // Recursively add files from the folder
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            // Skip directories
            if ($file->isDir()) {
                continue;
            }

            // Get the file path relative to the folder
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folderPath) + 1);

            // Add file to the ZIP archive
            if (!$zip->addFile($filePath, $relativePath)) {
                throw new \Exception("Failed to add file to ZIP: $filePath");
            }
        }

        // Close the ZIP file
        if (!$zip->close()) {
            throw new \Exception('Failed to finalize ZIP file');
        }
    }
}
