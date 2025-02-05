<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class ToYolo extends BaseMapper
{
    private array $classMap = [];

    /**
     * @throws \Exception
     */
    public function mapAnnotations($images, $datasetPath): void
    {
        foreach ($images as $image) {
            $annotationPath = $datasetPath . '/' . YoloConfig::LABELS_FOLDER . '/' . pathinfo($image['filename'], PATHINFO_FILENAME) . '.' . YoloConfig::TXT_EXTENSION;

            foreach($image['annotations'] as $annotation) {
                $dbClassId = $annotation['class']['id'];

                if (!isset($this->classMap[$dbClassId])) {
                    $newClassId = count($this->classMap);
                    $this->classMap[$dbClassId] = [
                        'id' => $newClassId,
                        'name' => $annotation['class']['name'],
                    ];
                }

                $mappedAnnotation = $annotation['segmentation'] ? $this->mapPolygon($annotation) : $this->mapBbox($annotation);
                if (false === Storage::append($annotationPath, $mappedAnnotation)) {
                    throw new \Exception("Failed to write annotation to file");
                }
            }
        }
        $this->populateDataFile($datasetPath);
    }

    public function linkImages($images, $datasetPath): void
    {
        foreach ($images as $image) {
            $source = storage_path($image['path']);
            $destination = storage_path($datasetPath) . '/' . YoloConfig::IMAGE_FOLDER . '/' . $image['filename'];

            // Ensure destination directory exists
            $destinationDir = dirname($destination);
            File::ensureDirectoryExists($destinationDir);

            // Create symbolic link using PHP's native symlink function
            if (File::exists($source)) {
                if (File::link($source, $destination)) {
                    throw new \Exception("Failed to link image: $source");
                }
            } else {
                throw new \Exception("Image not found: $source");
            }
        }
    }

    private function mapBbox(mixed $annotation): string
    {
        // Centering x and y coordinates because In DB they are stored as top-left corner
        $classId = $this->classMap[$annotation['class']['id']]['id'];
        $x = $annotation['x'] + $annotation['width'] / 2;
        $y = $annotation['y'] + $annotation['height'] / 2;
        $width = $annotation['width'];
        $height = $annotation['height'];

        return "$classId $x $y $width $height";
    }

    private function mapPolygon(mixed $annotation): string
    {
        $classId = $this->classMap[$annotation['class']['id']]['id'];
        $polygon = json_decode($annotation['segmentation'], true);

        $points = '';
        foreach ($polygon as $point) {
            $points .= $point['x'] . ' ' . $point['y'] . ' ';
        }

        return "$classId " . rtrim($points, ' ');
    }

    /**
     * @throws \Exception
     */
    private function populateDataFile($datasetPath): void
    {
        uasort($this->classMap, function ($a, $b) {
            return $a['id'] <=> $b['id'];
        });

        $yamlData = [
            'train' => 'images/train',
            'val' => 'images/val',
            'test' => 'images/test', // Optional
            'nc' => count($this->classMap),
            'names' => array_column($this->classMap, 'name'),
        ];

        if(false == Storage::put($datasetPath . '/data.yaml', Yaml::dump($yamlData, 2, 4))) {
            throw new \Exception("Failed to write data file");
        }
    }

}
