<?php

namespace App\ImportService\Validators\Labelme;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\YoloConfig;
use App\Exceptions\DataException;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class LabelmeZipValidator extends BaseZipValidator
{
    protected static string $configClass = LabelmeConfig::class;

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
