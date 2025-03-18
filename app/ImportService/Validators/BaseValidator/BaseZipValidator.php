<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DatasetImportException;
use App\ImportService\Interfaces\ZipValidatorInterface;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

abstract class BaseZipValidator implements ZipValidatorInterface
{
    const array IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * @throws DatasetImportException
     */
    public function validateImageOrganization(string $filePath, string $imageFolder): void
    {
        $imagesPath = $filePath . '/' . $imageFolder;
        $images = collect(Storage::files($imagesPath));

        // Filter out invalid image files
        $invalidImages = $images->filter(function ($image) {
            return !in_array(pathinfo($image, PATHINFO_EXTENSION), self::IMAGE_EXTENSIONS);
        });

        if ($invalidImages->isNotEmpty()) {
            throw new DatasetImportException("Invalid image files found", $invalidImages->toArray());
        }
    }

    /**
     * @throws DatasetImportException
     */
    public function validateAnnotationOrganization(string $filePath, string $labelExtension, $annotationFolder = null): void
    {
        $labelsPath = $filePath . '/' . $annotationFolder;
        $labels = collect(Storage::files($labelsPath));

        // Filter out invalid label files
        $invalidLabels = $labels->filter(function ($label) use ($labelExtension) {
            return !in_array(pathinfo($label, PATHINFO_EXTENSION), (array)$labelExtension);
        });

        if ($invalidLabels->isNotEmpty()) {
            throw new DatasetImportException("Invalid label files found", $invalidLabels->toArray());
        }
    }

    /**
     * @throws \Exception
     */
    protected function getPath(string $folderName): string
    {
        $path = AppConfig::LIVEWIRE_TMP_PATH . $folderName;
        if (!Storage::exists($path)) {
            throw new \Exception("Zip file not found");
        }
        return $path;
    }

}
