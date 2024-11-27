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

    public function findStructureErrors(string $datasetPath): array
    {
        return $this->zipValidator->validate($datasetPath);
    }

    public function findAnnotationIssues(string $datasetPath): array
    {
        return $this->annotationValidator->validate($datasetPath);
    }

    public function parseDataset(string $datasetPath, $annotationTechnique): array
    {
        return $this->importer->parse($datasetPath, $annotationTechnique);
    }
}
