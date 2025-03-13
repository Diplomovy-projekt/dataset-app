<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Utils\Response;

abstract class BaseAnnotationValidator
{
    abstract public function validate(string $datasetFolder, string $annotationTechnique): Response;
}
