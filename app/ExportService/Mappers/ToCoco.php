<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\CocoConfig;
use App\Configs\AppConfig;
use App\Utils\Util;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ToCoco extends BaseToMapper
{
    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        $cocoJsonPath = $this->getAnnotationDestinationPath($datasetFolder);

        $cocoJson = [
            'images' => [],
            'annotations' => [],
            'categories' => [],
        ];

        // Create images
        foreach ($images as $index => $image) {
            $cocoJson['images'][] = [
                'id' => $index,
                'file_name' => $image['filename'],
                'height' => $image['height'],
                'width' => $image['width'],
            ];

            // Create annotations
            foreach($image['annotations'] as $annotation) {
                $this->mapClass($annotation['class']['name']);

                $imgDims = [$image['width'], $image['height']];

                $bbox = $this->mapAnnotation(AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'], $annotation, $imgDims);
                $polygon = $this->mapAnnotation(AppConfig::ANNOTATION_TECHNIQUES['POLYGON'], $annotation, $imgDims);
                $area = $this->calculateArea($annotation, $imgDims);
                $cocoJson['annotations'][] = [
                    'id' => count($cocoJson['annotations']),
                    'image_id' => $index,
                    'category_id' => $this->getClassId($annotation['class']['name']),
                    'area' => $area,
                    'bbox' => $bbox,
                    'segmentation' => $polygon,
                ];

            }
        }

        // Create categories
        foreach ($this->classMap as $class) {
            $cocoJson['categories'][] = [
                'id' => $class['id'],
                'name' => $class['name'],
            ];
        }

        // Save the coco json file
        if(!Storage::put($cocoJsonPath, json_encode($cocoJson))) {
            throw new \Exception("Failed to write annotation to file");
        }
    }

    public function getImageDestinationDir($datasetFolder): string
    {
        $path = AppConfig::DATASETS_PATH['public'] . $datasetFolder . '/' . CocoConfig::IMAGE_FOLDER . '/' . $image['filename'];
        return Storage::path($path);
    }

    public function getAnnotationDestinationPath($datasetFolder, $image = null): string
    {
        return AppConfig::DATASETS_PATH['public'] .
            $datasetFolder . '/' .
            CocoConfig::LABELS_FILE;
    }

    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        if (empty($annotation['segmentation'])) {
            return [];
        }

        $segmentation = json_decode($annotation['segmentation'], true);
        $polygon = [];

        foreach ($segmentation as $point) {
            $polygon[] = Util::formatNumber($point['x'] * $imgDims[0]);
            $polygon[] = Util::formatNumber($point['y'] * $imgDims[1]);
        }

        return [$polygon];
    }

    public function mapBbox(mixed $annotation, array $imgDims = null): mixed
    {
        return [
            Util::formatNumber($annotation['x'] * $imgDims[0]),
            Util::formatNumber($annotation['y'] * $imgDims[1]),
            Util::formatNumber($annotation['width'] * $imgDims[0]),
            Util::formatNumber($annotation['height'] * $imgDims[1]),
        ];
    }

    private function calculateArea(mixed $annotation, array $imgDims = null): float
    {
        // Prefer area of segmentation if available
        if (!empty($annotation['segmentation'])) {
            $segmentation = json_decode($annotation['segmentation'], true);
            $n = count($segmentation);
            $area = 0;

            for ($i = 0; $i < $n; $i++) {
                $j = ($i + 1) % $n;
                $x1 = $segmentation[$i]['x'] * $imgDims[0];
                $y1 = $segmentation[$i]['y'] * $imgDims[1];
                $x2 = $segmentation[$j]['x'] * $imgDims[0];
                $y2 = $segmentation[$j]['y'] * $imgDims[1];

                $area += $x1 * $y2 - $x2 * $y1;
            }

            return  Util::formatNumber(abs($area) / 2);
        }

        // Fallback to bounding box area
        return Util::formatNumber($annotation['width'] * $imgDims[0]) * Util::formatNumber($annotation['height'] * $imgDims[1]);
    }


    public function getImageFolder(): string
    {
        return CocoConfig::IMAGE_FOLDER;
    }
}
