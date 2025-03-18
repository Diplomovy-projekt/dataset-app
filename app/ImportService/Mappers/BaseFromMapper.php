<?php

namespace App\ImportService\Mappers;

use App\ImportService\Interfaces\FromMapperInterface;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

abstract class BaseFromMapper implements FromMapperInterface
{

    /**
     * Adds unique suffixes to image and annotation file names to ensure they are unique.
     *
     * @param \Illuminate\Support\Collection $images Collection of image file paths.
     * @param \Illuminate\Support\Collection $annotations Collection of annotation file paths.
     * @throws \Exception If the count of images and annotations do not match.
     */
    public function addUniqueSuffixes(Collection &$images, Collection &$annotations)
    {
        $images = $images->sort()->values();
        $annotations = $annotations->sort()->values();

        if ($images->count() !== $annotations->count()) {
            throw new \Exception("Mismatched count: Images and annotations do not align.");
        }

        foreach ($images as $index => $image) {
            $suffix = uniqid('_da_');

            // Update the image
            $newImagePath = FileUtil::addUniqueSuffix($image, $suffix);
            Storage::move($image, $newImagePath);
            $images[$index] = $newImagePath;

            // Update the corresponding annotation
            $annotation = $annotations[$index];
            $newAnnotationPath = FileUtil::addUniqueSuffix($annotation, $suffix);
            Storage::move($annotation, $newAnnotationPath);
            $annotations[$index] = $newAnnotationPath;
        }
    }
    abstract function parse(string $folderName, $annotationTechnique): Response;
    abstract function transformBoundingBox(array $bbox, array $imgDims = null): array;
    abstract function transformPolygon(array $polygonPoints, array $imgDims = null): string;
    abstract function getClasses($classesSource): array;


}
