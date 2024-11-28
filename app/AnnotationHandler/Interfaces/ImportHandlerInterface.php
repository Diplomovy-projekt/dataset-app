<?php

namespace App\AnnotationHandler\Interfaces;

use App\Utils\Response;

interface ImportHandlerInterface
{
    public function findStructureErrors(string $folderName): array;

    public function findAnnotationIssues(string $folderName, string $annotationTechnique): array;

    public function parseDataset(string $folderName, string $annotationTechnique): array;
}
