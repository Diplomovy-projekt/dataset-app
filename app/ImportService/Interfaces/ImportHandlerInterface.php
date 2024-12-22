<?php

namespace App\ImportService\Interfaces;

interface ImportHandlerInterface
{
    public function findStructureErrors(string $folderName): array;

    public function findAnnotationIssues(string $folderName, string $annotationTechnique): array;

    public function parseDataset(string $folderName, string $annotationTechnique): array;
}
