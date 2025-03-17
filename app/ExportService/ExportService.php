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
        try {
            $datasetFolder = uniqid('custom_dataset_build_');
            $mapper = ExportComponentFactory::createMapper($format);
            if($mapper instanceof Response) {
                throw new \Exception('Invalid export format');
            }
            //1. Create and map the dataset folder
            $mapper->handle($images, $datasetFolder, $annotationTechnique);

            //2. Create a zip file from the dataset folder
            $absolutePath = Storage::disk('datasets')->path($datasetFolder);
            ZipManager::createZipFromFolder($absolutePath);

            //3. Delete the dataset folder and create job to delete zip
            Storage::disk('datasets')->deleteDirectory($datasetFolder);
            DeleteTempFile::dispatch(AppConfig::DATASETS_PATH['public'] . $datasetFolder . '.zip')
                ->delay(now()->add(AppConfig::EXPIRATION['TMP_FILE']['value'], AppConfig::EXPIRATION['TMP_FILE']['unit']))
                ->onQueue('temp-files');

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
