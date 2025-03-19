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
    /**
     * @throws DataException
     */
    public function validateStructure(string $folderName): void
    {
        $filePath = $this->getPath($folderName);

        $this->validateDataFile($filePath);
        $this->validateImageOrganization($filePath, YoloConfig::IMAGE_FOLDER);
        $this->validateAnnotationOrganization($filePath, YoloConfig::LABEL_EXTENSION, YoloConfig::LABELS_FOLDER);
    }

    /**
     * @throws DataException
     */
    private function validateDataFile(string $filePath): void
    {
        $dataFilePath = $filePath . '/' . YoloConfig::DATA_YAML;
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
