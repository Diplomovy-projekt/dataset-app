<?php

namespace App\ImportService\Mappers;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class FromLabelme extends BaseFromMapper
{
    private array $classMap = [];
    private int $classCounter = 0;
    public function parse(string $folderName, $annotationTechnique): Response
    {
        // Define folder paths
        $datasetPath = AppConfig::LIVEWIRE_TMP_PATH . $folderName;
        $annotationFolder = $datasetPath . '/' . LabelmeConfig::LABELS_FOLDER;

        // Get list of annotation files
        $annotationFiles = collect(Storage::files($annotationFolder));

        // Parse annotations and get unique class names
        $imageData = $this->parseAnnotationFiles($annotationFiles, $annotationTechnique);
        $classes = $this->getClasses($annotationFiles);

        if (empty($imageData)) {
            return Response::error("Failed to map annotations");
        }

        return Response::success(data: [
            'images' => $imageData,
            'classes' => $classes,
        ]);
    }

    private function parseAnnotationFiles($annotationFiles, $annotationTechnique): array
    {
        $imageData = [];

        foreach ($annotationFiles as $index => $annotationFile) {
            $content = Storage::get($annotationFile);
            $data = json_decode($content, true);

            // Get image dimensions
            $imageWidth = $data['imageWidth'];
            $imageHeight = $data['imageHeight'];

            // Get image size
            $imageDir = dirname($annotationFile, 2) . '/' . LabelmeConfig::IMAGE_FOLDER;
            $imagePath = $imageDir . '/' . $data['imagePath'];
            $absolutePath = Storage::path($imagePath);
            $imgSize = filesize($absolutePath);

            // Build data
            $imageData[$index] = [
                'filename' => $data['imagePath'],
                'width' => $imageWidth,
                'height' => $imageHeight,
                'size' => $imgSize,
                'annotations' => $this->parseShapes($data['shapes'], $annotationTechnique, $imageWidth, $imageHeight),
            ];
        }

        return $imageData;
    }

    private function parseShapes(array $shapes, string $annotationTechnique, int $imageWidth, int $imageHeight): array
    {
        $annotations = [];

        foreach ($shapes as $index => $shape) {

            $imgDims = ['width' => $imageWidth, 'height' => $imageHeight];

            if (!isset($this->classMap[$shape['label']])) {
                $this->classMap[$shape['label']] = $this->classCounter++;
            }
            $classId = $this->classMap[$shape['label']];
            $annotation = [
                'class_id' => $classId,
            ];

            if ($annotationTechnique == AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                $annotation += $this->transformBoundingBox($shape['points'], $imgDims);
            } elseif ($annotationTechnique == AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                $annotation += $this->createBboxFromPolygon($shape['points'], $imgDims);
                $annotation['segmentation'] = $this->transformPolygon($shape['points'], $imgDims);
            }

            $annotations[] = $annotation;
        }

        return $annotations;
    }

    public function transformBoundingBox(array $bbox, array $imgDims = null): array
    {
        // Labelme rectangle format has two points: top-left and bottom-right
        $x1 = $bbox[0][0];
        $y1 = $bbox[0][1];
        $x2 = $bbox[1][0];
        $y2 = $bbox[1][1];

        // Calculate width and height
        $width = $x2 - $x1;
        $height = $y2 - $y1;

        // Normalize values
        $x1 /= $imgDims['width'];
        $y1 /= $imgDims['height'];
        $width /= $imgDims['width'];
        $height /= $imgDims['height'];


        return [
            'x' => $x1,
            'y' => $y1,
            'width' => $width,
            'height' => $height,
        ];
    }

    public function transformPolygon(array $polygonPoints, array $imgDims = null): string
    {
        $normalizedPoints = [];

        foreach ($polygonPoints as $point) {
            $x = $point[0];
            $y = $point[1];

            // Normalize values
            $x /= $imgDims['width'];
            $y /= $imgDims['height'];

            $normalizedPoints[] = ['x' => $x, 'y' => $y];
        }

        return json_encode($normalizedPoints);
    }

    private function createBboxFromPolygon(array $points, array $imgDims = null): array
    {
        // Extract x and y values from points
        $xCoords = array_column($points, 0);
        $yCoords = array_column($points, 1);

        $minX = min($xCoords);
        $minY = min($yCoords);
        $maxX = max($xCoords);
        $maxY = max($yCoords);

        $width = $maxX - $minX;
        $height = $maxY - $minY;

        // Normalize values
        $minX /= $imgDims['width'];
        $minY /= $imgDims['height'];
        $width /= $imgDims['width'];
        $height /= $imgDims['height'];

        return [
            'x' => $minX,
            'y' => $minY,
            'width' => $width,
            'height' => $height
        ];
    }

    public function getClasses($classesSource): array
    {
        $classNames = [];

        foreach ($classesSource as $file) {
            $content = Storage::get($file);
            $data = json_decode($content, true);

            if (!$data || !isset($data['shapes'])) {
                continue;
            }

            foreach ($data['shapes'] as $shape) {
                if (!empty($shape['label']) && !in_array($shape['label'], $classNames)) {
                    $classNames[$shape['label']] = [
                        'name' => $shape['label'],
                    ];
                }
            }
        }

        return $classNames;
    }
}
