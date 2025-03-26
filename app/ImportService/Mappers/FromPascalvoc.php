<?php

namespace App\ImportService\Mappers;

use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class FromPascalvoc extends BaseFromMapper
{
    private array $classMap = [];
    private int $classCounter = 0;

    public function parse(string $folderName, $annotationTechnique): Response
    {
        // Define folder paths
        $datasetPath = AppConfig::LIVEWIRE_TMP_PATH . $folderName;

        // Get all XML files in the dataset folder
        $allFiles = collect(Storage::files($datasetPath));
        $annotationFiles = $allFiles->filter(function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'xml';
        });

        // Parse annotations and get unique class names
        $imageData = $this->parseAnnotationFiles($annotationFiles, $annotationTechnique, $datasetPath);
        $classes = $this->getClasses($annotationFiles);

        if (empty($imageData)) {
            return Response::error("Failed to map annotations");
        }

        return Response::success(data: [
            'images' => $imageData,
            'classes' => $classes,
        ]);
    }

    private function parseAnnotationFiles($annotationFiles, $annotationTechnique, $datasetPath): array
    {
        $imageData = [];

        foreach ($annotationFiles as $index => $annotationFile) {
            $content = Storage::get($annotationFile);

            // Parse XML content
            $xml = simplexml_load_string($content);
            if (!$xml) {
                continue;
            }

            // Get filename
            $filename = (string)$xml->filename;

            // Get image dimensions
            $imageWidth = (int)$xml->size->width;
            $imageHeight = (int)$xml->size->height;

            // Get image size
            $imagePath = $datasetPath . '/' . $filename;
            $absolutePath = Storage::path($imagePath);
            $imgSize = file_exists($absolutePath) ? filesize($absolutePath) : 0;

            // Build annotations
            $annotations = [];
            if (isset($xml->object)) {
                $annotations = $this->parseObjects($xml->object, $annotationTechnique, $imageWidth, $imageHeight);
            }

            // Build data
            $imageData[$index] = [
                'filename' => $filename,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'size' => $imgSize,
                'annotations' => $annotations,
            ];
        }

        return $imageData;
    }

    private function parseObjects($objects, $annotationTechnique, $imageWidth, $imageHeight): array
    {
        $annotations = [];
        $imgDims = ['width' => $imageWidth, 'height' => $imageHeight];

        foreach ($objects as $object) {
            $className = (string)$object->name;

            if (!isset($this->classMap[$className])) {
                $this->classMap[$className] = $this->classCounter++;
            }

            $classId = $this->classMap[$className];
            $annotation = [
                'class_id' => $classId,
            ];

            if ($annotationTechnique == AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                $bbox = [
                    (float)$object->bndbox->xmin,
                    (float)$object->bndbox->ymin,
                    (float)$object->bndbox->xmax,
                    (float)$object->bndbox->ymax
                ];

                $annotation += $this->transformBoundingBox($bbox, $imgDims);
            } elseif ($annotationTechnique == AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                $polygonPoints = json_decode(json_encode($object->polygon), true);
                $annotation += $this->createBboxFromPolygon($polygonPoints, $imgDims);
                $annotation['segmentation'] = $this->transformPolygon($polygonPoints, $imgDims);

            }

            $annotations[] = $annotation;
        }

        return $annotations;
    }

    public function transformBoundingBox(array $bbox, array $imgDims = null): array
    {
        // Pascal VOC format: [xmin, ymin, xmax, ymax]
        $xmin = $bbox[0];
        $ymin = $bbox[1];
        $xmax = $bbox[2];
        $ymax = $bbox[3];

        // Calculate width and height
        $width = $xmax - $xmin;
        $height = $ymax - $ymin;

        // Normalize values
        $xmin /= $imgDims['width'];
        $ymin /= $imgDims['height'];
        $width /= $imgDims['width'];
        $height /= $imgDims['height'];

        return [
            'x' => $xmin,
            'y' => $ymin,
            'width' => $width,
            'height' => $height,
        ];
    }

    public function transformPolygon(array $polygonPoints, array $imgDims = null): array
    {
        $normalizedPoints = [];
        foreach (array_chunk($polygonPoints, 2) as [$x, $y]) {
            $normalizedPoints[] = ['x' => $x / $imgDims['width'], 'y' => $y / $imgDims['height']];
        }
        return $normalizedPoints;
    }

    private function createBboxFromPolygon(array $polygonPoints, array $imgDims = null): array
    {
        $xCoords = array_column(array_chunk($polygonPoints, 2), 0);
        $yCoords = array_column(array_chunk($polygonPoints, 2), 1);

        $minX = min($xCoords);
        $minY = min($yCoords);
        $width = max($xCoords) - $minX;
        $height = max($yCoords) - $minY;

        return [
            'x' => $minX / $imgDims['width'],
            'y' => $minY / $imgDims['height'],
            'width' => $width / $imgDims['width'],
            'height' => $height / $imgDims['height']
        ];
    }

    public function getClasses($classesSource): array
    {
        $classNames = [];

        foreach ($classesSource as $file) {
            $content = Storage::get($file);
            $xml = simplexml_load_string($content);

            if (!$xml || !isset($xml->object)) {
                continue;
            }

            foreach ($xml->object as $object) {
                $className = (string)$object->name;
                if (!empty($className) && !isset($classNames[$className])) {
                    $classNames[$className] = [
                        'name' => $className,
                    ];
                }
            }
        }

        return $classNames;
    }
}
