<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\CocoConfig;
use App\Configs\AppConfig;
use App\Utils\Util;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ToCoco extends BaseToMapper
{
    protected static string $configClass = CocoConfig::class;

    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        $cocoJsonPath = $this->getAnnotationDestinationPath($datasetFolder);

        if (!Storage::exists($cocoJsonPath)) {
            $cocoJson = [
                'images' => [],
                'annotations' => [],
                'categories' => [],
            ];
        } else {
            $cocoJson = json_decode(Storage::get($cocoJsonPath), true);
        }

        // Create images
        foreach ($images as $index => $image) {

            if(!$this->imageExists($cocoJson['images'], $image['filename'])) {
                $index = count($cocoJson['images']);

                $cocoJson['images'][] = [
                    'id' => $index,
                    'file_name' => $image['filename'],
                    'height' => $image['height'],
                    'width' => $image['width'],
                ];
            } else {
                $index = array_search($image['filename'], array_column($cocoJson['images'], 'file_name'));
            }

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
            if(!in_array($class['id'], array_column($cocoJson['categories'], 'id'))) {
                $cocoJson['categories'][] = [
                    'id' => $class['id'],
                    'name' => $class['name'],
                    'supercategory' => $class['supercategory'] ?? null,
                ];
            }
        }

        // Save the coco json file
        if(!Storage::put($cocoJsonPath, json_encode($cocoJson))) {
            throw new \Exception("Failed to write annotation to file");
        }
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

        $segmentation = $annotation['segmentation'];
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
            $segmentation = $annotation['segmentation'];
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

    private function imageExists(array $images, string $filename): bool
    {
        return in_array($filename, array_column($images, 'file_name'));
    }
}
