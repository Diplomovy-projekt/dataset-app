<?php

namespace App\ImportService\Mappers;

use App\Configs\Annotations\CocoConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class FromCoco extends BaseMapper
{

    function parse(string $folderName, $annotationTechnique): Response
    {
        // Define folder paths
        $datasetPath = AppConfig::LIVEWIRE_TMP_PATH . $folderName;
        $annotationFolder = $datasetPath . '/' . CocoConfig::LABELS_FILE;

        // Get list of image and annotation files
        $cocoJson = Storage::get($annotationFolder);
        $cocoJson = json_decode($cocoJson, true);
        $annotations = $cocoJson['annotations'];
        $images = $cocoJson['images'];
        $categories = $cocoJson['categories'];

        $imageData = $this->parseImages($images, $annotations, $folderName, $annotationTechnique);
        $classes = $this->getClasses($categories);

        return $imageData && $classes
            ? Response::success(data:[
                'images' => $imageData,
                'classes' => $classes,
            ])
            : Response::error("Failed to map annotations");
    }

    private function parseImages(mixed $images, mixed $annotations, $folderName, $annotationTechnique): array
    {
        $imageData = [];

        foreach ($images as $image) {
            $imgDims = [$image['width'], $image['height']];
            $path = Storage::path(AppConfig::LIVEWIRE_TMP_PATH . $folderName . '/' . CocoConfig::IMAGE_FOLDER . '/' . basename($image['file_name']));
            $imgSize = filesize($path);
            $imageData[] = [
                'filename' => basename($image['file_name']),
                'width' => $image['width'],
                'height' => $image['height'],
                'size' => $imgSize,
                'annotations' => $this->parseAnnotationsPerImage($image['id'], $annotations, $imgDims ,$annotationTechnique),
            ];
        }

        return $imageData;
    }
    private function parseAnnotationsPerImage(mixed $id, mixed $annotations, $imgDims, $annotationTechnique): array
    {
        $imageAnnotations = [];
        foreach ($annotations as $annotation) {
            if ($annotation['image_id'] == $id) {
                $classId = $annotation['category_id'];
                $bbox = $annotation['bbox'];

                $imageAnnotation = [
                    'class_id' => $classId,
                ];
                $imageAnnotation += $this->transformBoundingBox($bbox, $imgDims);
                if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                    $imageAnnotation['segmentation'] = $this->transformPolygon($annotation['segmentation'][0], $imgDims);
                }
                $imageAnnotations[] = $imageAnnotation;
            }
        }
        return $imageAnnotations;
    }

    public function transformBoundingBox(array $bbox, $imgDims = null): array
    {
        $x = $bbox[0];
        $y = $bbox[1];
        $width = $bbox[2];
        $height = $bbox[3];

        // Normalize the values to the range [0, 1]
        $normalizedX = $x / $imgDims[0];
        $normalizedY = $y / $imgDims[1];
        $normalizedWidth = $width / $imgDims[0];
        $normalizedHeight = $height / $imgDims[1];

        return [
            'x' => $normalizedX,
            'y' => $normalizedY,
            'width' => $normalizedWidth,
            'height' => $normalizedHeight,
        ];
    }


    function transformPolygon( array $polygonPoints, array $imgDims = null): string
    {
        $normalizedPoints = [];
        foreach (array_chunk($polygonPoints, 2) as $pair) {
            // normalize the values to the range [0, 1]
            $pair[0] = $pair[0] / $imgDims[0];
            $pair[1] = $pair[1] / $imgDims[1];
            $normalizedPoints[] = ['x' => $pair[0], 'y' => $pair[1]];
        }

        return json_encode($normalizedPoints);
    }

    function getClasses($classesSource): array
    {
        return array_map(fn($class) => [
            'name' => $class['name'],
            'supercategory' => $class['supercategory'] ?? null
        ], $classesSource);
    }

}
