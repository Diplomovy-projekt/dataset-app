<?php

namespace App\ImportService\Interfaces;

use App\Utils\Response;

interface AnnotationValidatorInterface
{
    /**
     * Validates annotations in the dataset folder using the given annotation technique.
     *
     * @param string $datasetFolder The folder containing the dataset.
     * @param string $annotationTechnique The annotation technique used.
     * @return Response Validation result.
     */
    public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response;
}
