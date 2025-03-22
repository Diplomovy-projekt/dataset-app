<?php

namespace App\ExportService\Mappers;

class ToPascalvoc extends BaseToMapper
{

    public function getImageFolder(): string
    {
        // TODO: Implement getImageFolder() method.
    }

    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        // TODO: Implement createAnnotations() method.
    }

    public function getAnnotationDestinationPath(string $datasetFolder, array $image = null): string
    {
        // TODO: Implement getAnnotationDestinationPath() method.
    }

    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        // TODO: Implement mapPolygon() method.
    }

    public function mapBbox(mixed $annotation, array $imgDims = null): mixed
    {
        // TODO: Implement mapBbox() method.
    }
}
