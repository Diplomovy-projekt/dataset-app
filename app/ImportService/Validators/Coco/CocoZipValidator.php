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

    protected static string $configClass = CocoConfig::class;

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
}
