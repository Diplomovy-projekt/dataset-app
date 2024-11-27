<?php

namespace App\AnnotationHandler\Importers\COCO;

use App\AnnotationHandler\Interfaces\ImporterInterface;
use App\AnnotationHandler\Interfaces\ValidatorInterface;

class COCOValidator implements ValidatorInterface
{


    public function validateArchiveStructure(string $archivePath)
    {
        // TODO: Implement validateArchiveStructure() method.
    }

    public function validateAnnotationFormat(string $annotationsPath)
    {
        // TODO: Implement validateAnnotationFormat() method.
    }
}
