<?php

namespace App\ImportService\Validators\Pascalvoc;

use App\Configs\Annotations\CocoConfig;
use App\Configs\Annotations\PascalvocConfig;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;

class PascalvocZipValidator extends BaseZipValidator
{
    protected static string $configClass = PascalvocConfig::class;

    protected function validateImageOrganization(string $datasetPath, ?string $imageFolder): void
    {
        $folderPath = $imageFolder ? $datasetPath . '/' . $imageFolder : $datasetPath;
        $allowedExtensions = array_merge(self::IMAGE_EXTENSIONS, (array) self::$configClass::LABEL_EXTENSION);
        $this->validateFolderContent($folderPath, $allowedExtensions);

    }

    protected function validateAnnotationOrganization(string $datasetPath, string $labelExtension, ?string $annotationFolder = null): void
    {
        $folderPath = $annotationFolder ? $datasetPath . '/' . $annotationFolder : $datasetPath;
        $allowedExtensions = array_merge(self::IMAGE_EXTENSIONS, (array) self::$configClass::LABEL_EXTENSION);
        $this->validateFolderContent($folderPath, $allowedExtensions);
    }
}
