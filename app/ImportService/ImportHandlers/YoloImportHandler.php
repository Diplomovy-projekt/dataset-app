<?php

namespace App\ImportService\ImportHandlers;

use App\Configs\Annotations\YoloConfig;
use App\ImportService\Importers\YoloImporter;
use App\ImportService\Interfaces\ImportHandlerInterface;
use App\ImportService\Validators\Yolo\YoloAnnotationValidator;
use App\ImportService\Validators\Yolo\YoloZipValidator;

class YoloImportHandler implements ImportHandlerInterface
{
    use YoloConfig;
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
