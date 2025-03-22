<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\PaligemmaConfig;

class ToPaligemma extends BaseToMapper
{
    protected static string $configClass = PaligemmaConfig::class;

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
