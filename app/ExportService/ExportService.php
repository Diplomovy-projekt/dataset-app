<?php

namespace App\ExportService;

use App\Configs\AppConfig;
use App\ExportService\Factory\ExportComponentFactory;
use App\FileManagement\ZipManager;
use App\Utils\Response;
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
        $this->mapper = ExportComponentFactory::createMapper($format);

        try {
            $datasetFolder = uniqid('custom_dataset_build_');
            $this->mapper->handle($images, $datasetFolder);

            $absolutePath = Storage::disk('datasets')->path($datasetFolder);
            ZipManager::createZipFromFolder($absolutePath);

            return Response::success(data: ['datasetFolder' => $datasetFolder.'.zip']);
        }catch (\Exception $e) {
            if(Storage::disk('datasets')->exists($datasetFolder)) {
                Storage::disk('datasets')->deleteDirectory($datasetFolder);
            }
            if(Storage::disk('datasets')->exists($datasetFolder.'.zip')) {
                Storage::disk('datasets')->delete($datasetFolder.'.zip');
            }
            return Response::error("An error occurred while exporting the dataset: " . $e->getMessage());
        }
    }
}
