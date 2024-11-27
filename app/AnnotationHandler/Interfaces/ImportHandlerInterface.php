<?php

namespace App\AnnotationHandler\Interfaces;

use App\Utils\Response;

interface ImportHandlerInterface
{
    public function findStructureErrors(string $datasetPath): array;

    public function findAnnotationIssues(string $datasetPath): array;

    public function parseDataset(string $datasetPath, string $annotationTechnique): array;
}
