<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

abstract class BaseAnnotationValidator
{
    /**
     * Get the path to the annotation folder (if format uses one file per image)
     * or the path to the annotation file (if format uses one file for all images).
     *
     * @throws \Exception
     */
    public function getAnnotationPath(string $datasetFolder, ?string $annotationFolder): string
    {
        $path = AppConfig::LIVEWIRE_TMP_PATH . $datasetFolder;

        if ($annotationFolder !== null) {
            $path .= '/' . $annotationFolder;
        }

        if (!Storage::exists($path)) {
            throw new \Exception("Annotation folder does not exist: $path");
        }

        return $path;
    }

    /**
     * This method needs to validate correctness of the annotation data,
     * because during the mapping process, the data is not validated again.
     *
     * It should return structured array of found issues to notify user about them.
     *
     * @param string $datasetFolder The folder containing the dataset.
     * @param string $annotationTechnique The annotation technique used.
     * @return Response Validation result with issues that are displayed to the user.
     */
    abstract public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response;
}
