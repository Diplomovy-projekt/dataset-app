<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ToLabelme extends BaseToMapper
{
    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);
            $labelmeJson = [
                'version' => "5.5.0",
                'flags' => [],
                'shapes' => [],
                'imagePath' => '../' . $this->getImageFolder() . '/' . $image['filename'],
                'imageData' => null,
                'imageHeight' => $image['height'],
                'imageWidth' => $image['width'],
            ];

            foreach ($image['annotations'] as $annotation) {
                $this->mapClass($annotation['class']['name']);
                $imgDims = [$image['width'], $image['height']];

                $labelmeJson['shapes'][] = [
                    'label' => $annotation['class']['name'],
                    'points' => $this->mapAnnotation($annotationTechnique, $annotation, $imgDims),
                    'group_id' => null,
                    'shape_type' => $annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'] ? 'rectangle' : 'polygon',
                    'flags' => [],
                    'mask' => null,
                ];
            }

            File::ensureDirectoryExists($annotationPath);
            if(!Storage::put($annotationPath, json_encode($labelmeJson))) {
                throw new \Exception("Failed to write annotation to file");
            }
        }
    }

    public function getAnnotationDestinationPath($datasetFolder, $image = null): string
    {
        return AppConfig::DATASETS_PATH['public'] .
            $datasetFolder . '/' .
            LabelmeConfig::LABELS_FOLDER . '/' .
            pathinfo($image['filename'], PATHINFO_FILENAME) . '.' .
            LabelmeConfig::LABEL_EXTENSION;
    }

    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        $polygon = json_decode($annotation['segmentation'], true);

        return array_map(fn($point) => [
            $point['x'] * $imgDims[0],
            $point['y'] * $imgDims[1]
        ], $polygon);
    }

    public function mapBbox(mixed $annotation, array $imgDims = null): array
    {
        // Labelme uses two points for rectangle: top-left and bottom-right
        $x = $annotation['x'];
        $y = $annotation['y'];
        $width = $annotation['width'];
        $height = $annotation['height'];

        $x *= $imgDims[0];
        $y *= $imgDims[1];
        $width *= $imgDims[0];
        $height *= $imgDims[1];

        return [
            [$x, $y],
            [$x + $width, $y + $height]
        ];
    }

    public function getImageFolder(): string
    {
        return LabelmeConfig::IMAGE_FOLDER;
    }
}
