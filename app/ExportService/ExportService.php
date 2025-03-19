<?php

namespace App\ExportService;

use App\Configs\AppConfig;
use App\ExportService\Factory\ExportComponentFactory;
use App\FileManagement\ZipManager;
use App\Jobs\DeleteTempFile;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class ExportService
{

    public static function handleExport($images, $format, $annotationTechnique)
    {
        $datasetFolderToDownload = uniqid('custom_dataset_build_');
        $datasetFolderToDownloadPath = AppConfig::DATASETS_PATH['public'] . $datasetFolderToDownload;

        try {
            $mapper = ExportComponentFactory::createMapper($format);

            //1. Create and map the dataset folder
            $mapper->handle($images, $datasetFolderToDownload, $annotationTechnique);

            //2. Create a zip file from the dataset folder
            $absolutePath = Storage::path($datasetFolderToDownloadPath);
            ZipManager::createZipFromFolder($absolutePath);

            //3. Delete the dataset folder and create job to delete zip
            Storage::deleteDirectory($datasetFolderToDownloadPath);
            DeleteTempFile::dispatch($datasetFolderToDownloadPath . '.zip')
                ->delay(now()->add(AppConfig::EXPIRATION['TMP_FILE']['value'], AppConfig::EXPIRATION['TMP_FILE']['unit']))
                ->onQueue('temp-files');

            return Response::success(data: ['datasetFolder' => $datasetFolderToDownload.'.zip']);
        }catch (\Exception $e) {
            if(Storage::exists($datasetFolderToDownloadPath)) {
                Storage::deleteDirectory($datasetFolderToDownloadPath);
            }
            if(Storage::exists($datasetFolderToDownloadPath.'.zip')) {
                Storage::delete($datasetFolderToDownloadPath.'.zip');
            }
            return Response::error("An error occurred while exporting the dataset: " . $e->getMessage());
        }
    }
}
