<?php

namespace App\ImportService\Interfaces;

use App\Utils\Response;
use Illuminate\Database\Eloquent\Collection;

interface FromMapperInterface
{
    /**
     * Adds unique suffixes to image and annotation file names to ensure uniqueness.
     *
     * @param \Illuminate\Support\Collection $images Collection of image file paths.
     * @param \Illuminate\Support\Collection $annotations Collection of annotation file paths.
     * @throws \Exception If the count of images and annotations do not match.
     */
    public function addUniqueSuffixes(Collection &$images, Collection &$annotations);

    /**
     * Parses a dataset folder using a given annotation technique.
     *
     * @param string $folderName The dataset folder name.
     * @param mixed $annotationTechnique The annotation technique used.
     * @return Response
     */
    public function parse(string $folderName, $annotationTechnique): Response;

    /**
     * Transforms a bounding box according to specific logic.
     *
     * @param array $bbox Bounding box coordinates.
     * @param array|null $imgDims Optional image dimensions.
     * @return array Transformed bounding box.
     */
    public function transformBoundingBox(array $bbox, array $imgDims = null): array;

    /**
     * Transforms polygon points into a formatted string or data structure.
     *
     * @param array $polygonPoints Polygon coordinate points.
     * @param array|null $imgDims Optional image dimensions.
     * @return string Transformed polygon data.
     */
    public function transformPolygon(array $polygonPoints, array $imgDims = null): array;

    /**
     * Retrieves class labels from a given source.
     *
     * @param mixed $classesSource Source of class labels.
     * @return array List of class names.
     */
    public function getClasses($classesSource): array;
}
