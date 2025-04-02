<?php

namespace App\ImportService\Mappers;

use App\ImportService\Interfaces\FromMapperInterface;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

abstract class BaseFromMapper
{
    /**
     * Entry point for parsing annotations. For this method you need to know the structure of the annotations.
     *
     * Parse the annotations for the given folder and technique.
     *
     * Expected structure of the returned array:
     * [
     *    [
     *        'filename' => 'image1.jpg',
     *        'width' => 1920,
     *        'height' => 1080,
     *        'size' => 345678,
     *        'annotations' => [
     *            [
     *                 'class_id' => 1,
     *                 'x' => 0.1,
     *                 'y' => 0.2,
     *                 'width' => 0.4,
     *                 'height' => 0.3,
     *                 'segmentation' => [[0.1, 0.2], [0.4, 0.2], [0.4, 0.5], [0.1, 0.5]]
     *             ],
     *        ],
     *    ],
     * ]
     *
     * @param string $folderName The name of the folder containing the dataset.
     * @param mixed $annotationTechnique The technique used for annotation.
     * @return Response which contains $iamges array and $classes array
     */
    abstract function parse(string $folderName, $annotationTechnique): Response;

    /**
     * Transform a bounding box based on image dimensions.
     * Expected structure of the bounding box:
     * [x, y, width, height]
     * @param array $bbox The bounding box coordinates [x, y, width, height].
     * @param array|null $imgDims Optional image dimensions [width, height].
     * @return array The transformed bounding box.
     */
    abstract function transformBoundingBox(array $bbox, array $imgDims = null): array;

    /**
     * Transform polygon points based on image dimensions.
     * Expected structure of the polygon points:
     * [[x1, y1], [x2, y2], [x3, y3], ...]
     * @param array $polygonPoints The polygon points as an array of coordinates.
     * @param array|null $imgDims Optional image dimensions [width, height] if needed.
     * @return array The transformed polygon points.
     */
    abstract function transformPolygon(array $polygonPoints, array $imgDims = null): array;

    /**
     * Retrieve class names from the provided source.
     * Returned array needs to be associative with class names as keys and class IDs as values.
     * [class_name => [
     *     'name' => class_name,
     *     'supercategory' => supercategory ?? null
     *     ]
     * ]
     * @param mixed $classesSource The source of the class definitions.
     * @return array An array of class names.
     */
    abstract function getClasses($classesSource): array;



}
