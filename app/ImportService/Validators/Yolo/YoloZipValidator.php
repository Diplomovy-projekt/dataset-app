<?php

namespace App\ImportService\Validators\Yolo;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class YoloZipValidator extends BaseZipValidator
{
    protected static string $configClass = YoloConfig::class;

    protected function validateImageOrganization(string $datasetPath, ?string $imageFolder): void
    {
        $folderPath = $imageFolder ? $datasetPath . '/' . $imageFolder : $datasetPath;
        $this->validateFolderContent($folderPath, self::IMAGE_EXTENSIONS);
    }

    protected function validateAnnotationOrganization(string $datasetPath, string $labelExtension, ?string $annotationFolder = null): void
    {
        $folderPath = $annotationFolder ? $datasetPath . '/' . $annotationFolder : $datasetPath;
        $this->validateFolderContent($folderPath, $labelExtension);
    }

    /**
     * Additional validation for the Yolo dataset.
     * Check if the data.yaml file is present and contains the 'nc' and 'names' keys.
     *
     * @throws DataException
     */
    protected function additionalStructureValidation(string $datasetPath): void
    {
        $dataFilePath = $datasetPath . '/' . YoloConfig::DATA_YAML;
        if (!Storage::exists($dataFilePath)) {
            throw new DataException("Data File not found");
        }
        // Read and parse the YAML file
        $dataContent = Storage::get($dataFilePath);
        $annotationData = Yaml::parse($dataContent);
        // check if 'nc' and 'names' keys are present
        if (!isset($annotationData['nc']) || !isset($annotationData['names'])) {
            throw new DataException("Missing 'nc' or 'names' key in data.yaml");
        }
    }

}
