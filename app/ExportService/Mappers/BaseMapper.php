<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class BaseMapper
{
    protected array $classMap = [];

    public function mapAnnotations($images, $datasetFolder): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);

            foreach($image['annotations'] as $annotation) {
                $dbClassId = $annotation['class']['id'];

                $idk = dirname($annotationPath);
                File::ensureDirectoryExists($annotationPath);

                if (!isset($this->classMap[$dbClassId])) {
                    $newClassId = count($this->classMap);
                    $this->classMap[$dbClassId] = [
                        'id' => $newClassId,
                        'name' => $annotation['class']['name'],
                    ];
                }

                $mappedAnnotation = $annotation['segmentation'] ? $this->mapPolygon($annotation) : $this->mapBbox($annotation);

                if (!Storage::append($annotationPath, $mappedAnnotation)) {
                    throw new \Exception("Failed to write annotation to file");
                }
            }
        }
    }

    public function linkImages($images, $datasetFolder): void
    {
        foreach ($images as $image) {
            $source = storage_path($image['path']);
            $destination = $this->getImageDestinationPath($datasetFolder, $image);

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
    abstract protected function getImageDestinationPath($datasetFolder, $image): string;

    abstract protected function getAnnotationDestinationPath($datasetFolder, $image): string;

    abstract protected function mapPolygon($annotation): string;

    abstract protected function mapBbox($annotation): string;
}
