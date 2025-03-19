<?php

namespace App\ImportService\Validators\Coco;

use App\Configs\Annotations\CocoConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class CocoZipValidator extends BaseZipValidator
{
    /**
     * @throws DataException
     * @throws \Exception
     */
    public function validateStructure(string $folderName): void
    {
        $filePath = $this->getPath($folderName);

        $this->validateImageOrganization($filePath, CocoConfig::IMAGE_FOLDER);
        $this->validateAnnotationOrganization($filePath, CocoConfig::LABEL_EXTENSION);
    }

    /**
     * @throws DataException
     */
    public function validateAnnotationOrganization(string $filePath, string $labelExtension, $annotationFolder = null): void
    {
        $labelsFile = $filePath . '/' . CocoConfig::LABELS_FILE;
        if (!Storage::exists($labelsFile)) {
            throw new DataException("COCO JSON file not found");
        }
    }
}
