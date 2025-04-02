<?php

namespace App\ImportService\Mappers;

use App\Utils\Response;

abstract class BaseFromMapper
{
    /**
     * Entry point for parsing annotations. For this method you need to know the structure of the annotations.
     *
     * Parse the annotations for the given folder and technique.
     *
     * Response Structure:
     * The method returns a Response object with the following data structure:
     *
     * On Success: Response::success() with data parameter containing:
     * [
     *   'images' => [
     *     [
     *       'filename' => string,      // The image filename (e.g., 'image1.jpg')
     *       'width' => int,            // Original image width in pixels
     *       'height' => int,           // Original image height in pixels
     *       'size' => int,             // File size in bytes
     *       'annotations' => [
     *         [
     *           'class_id' => int,     // ID of the class
     *           'x' => float,          // Normalized bounding box top-left X coordinate (0-1)
     *           'y' => float,          // Normalized bounding box top-left Y coordinate (0-1)
     *           'width' => float,      // Normalized bounding box width (0-1)
     *           'height' => float,     // Normalized bounding box height (0-1)
     *           'segmentation' => [    // Optional polygon points for segmentation masks
     *             [float, float],      // Each point as [x, y] normalized coordinates (0-1)
     *             // ...
     *           ]
     *         ],
     *         // More annotation entries...
     *       ]
     *     ],
     *     // More image entries...
     *   ],
     *   'classes' => [
     *     [
     *       'name' => string,          // Class name (e.g., 'person', 'car')
     *       'supercategory' => string|null  // Optional parent category
     *     ],
     *     // More class entries...
     *   ]
     * ]
     *
     * On Error: Response::error("Failed to map annotations")
     *
     * @param string $folderName The name of the folder containing the dataset.
     * @param mixed $annotationTechnique The technique used for annotation.
     * @return Response which contains $images array and $classes array
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
