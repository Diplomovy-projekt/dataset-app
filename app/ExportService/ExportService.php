<?php

namespace App\ExportService;

use App\Configs\AppConfig;
use App\ExportService\Factory\ExportComponentFactory;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    /**
     * @var \App\Utils\Response|mixed|object
     */
    private mixed $mapper;
    /**
     * @var \App\Utils\Response|mixed|object
     */
    private mixed $config;

    public function handleExport($images, $format)
    {
        //Create dataset folder
        $this->mapper = ExportComponentFactory::createMapper($format);
        $this->config = ExportComponentFactory::createConfig($format);

        $this->createFolderStructure($this->config->getFolderStructure());



    }

    private function createFolderStructure(array $folderStructure): string
    {
        // Generate a unique dataset name
        $datasetName = uniqid('custom_dataset_build_');
        $datasetPath = AppConfig::DATASETS_PATH . $datasetName;
        Storage::makeDirectory($datasetPath);

        $this->createFoldersRecursively($datasetPath, $folderStructure);

        return $datasetPath; // Return the path for further use
    }

    private function createFoldersRecursively(string $basePath, array $folderStructure): void
    {
        foreach ($folderStructure as $folder => $subFolders) {
            $path = $basePath . '/' . $folder;

            if ($subFolders === null) {
                // This is a file, not a folder
                Storage::put($path, '');
            } else {
                // This is a folder
                Storage::makeDirectory($path);

                // Recursively create subfolders if any
                if (is_array($subFolders) && !empty($subFolders)) {
                    $this->createFoldersRecursively($path, $subFolders);
                }
            }
        }
    }
}
