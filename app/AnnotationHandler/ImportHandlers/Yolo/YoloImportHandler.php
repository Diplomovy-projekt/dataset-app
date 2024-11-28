<?php

namespace App\AnnotationHandler\ImportHandlers\Yolo;

use App\AnnotationHandler\Importers\YoloImporter;
use App\AnnotationHandler\Interfaces\ImportHandlerInterface;
use App\AnnotationHandler\traits\Yolo\YoloFormatTrait;
use App\AnnotationHandler\Validators\Yolo\YoloAnnotationValidator;
use App\AnnotationHandler\Validators\Yolo\YoloZipValidator;
use App\Utils\Response;

class YoloImportHandler implements ImportHandlerInterface
{
    use YoloFormatTrait;
    private YoloZipValidator $zipValidator;
    private YoloAnnotationValidator $annotationValidator;
    private YoloImporter $importer;

    public function __construct() {
        $this->zipValidator = new YoloZipValidator();
        $this->annotationValidator = new YoloAnnotationValidator();
        $this->importer = new YoloImporter();
    }

    public function findStructureErrors(string $folderName): array
    {
        return $this->zipValidator->validate($folderName);
    }

    public function findAnnotationIssues(string $folderName, string $annotationTechnique): array
    {
        return $this->annotationValidator->validate($folderName, $annotationTechnique);
    }

    public function parseDataset(string $folderName, $annotationTechnique): array
    {
        return $this->importer->parse($folderName, $annotationTechnique);
    }
}
