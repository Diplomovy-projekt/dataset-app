<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\ImportService\Interfaces\AnnotationValidatorInterface;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

abstract class BaseAnnotationValidator implements AnnotationValidatorInterface
{
    /**
     * @throws \Exception
     */
    public function getAnnotationPath(string $datasetFolder, string $annotationTechnique): string
    {
        $path = AppConfig::LIVEWIRE_TMP_PATH . $datasetFolder .'/'. $annotationTechnique;
        if(!Storage::exists($path)) {
            throw new \Exception("Annotation folder does not exist: $path");
        }
        return $path;
    }
    /**
     * Validates annotations in the dataset folder using the given annotation technique.
     *
     * @param string $datasetFolder The folder containing the dataset.
     * @param string $annotationTechnique The annotation technique used.
     * @return Response Validation result.
     */
    abstract public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response;
}
