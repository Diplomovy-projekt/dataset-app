<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Utils\Util;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class BaseMapper
{
    protected array $classMap = [];


    /**
     * @throws \Exception
     */
    public function linkImages($images, $datasetFolder): void
    {
        $datasets = Dataset::whereIn('id', array_column($images, 'dataset_id'))->get()->keyBy('id');

        foreach ($images as $image) {
            $dataset = $datasets[$image['dataset_id']];
            $source = Util::getDatasetPath($dataset, true) . 'full-images/' . $image['filename'];
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

    /**
     * @throws \Exception
     */
    public function mapAnnotations($images, $datasetFolder, $annotationTechnique): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);

            foreach($image['annotations'] as $annotation) {
                $className = $annotation['class']['name'];

                File::ensureDirectoryExists($annotationPath);

                if (!isset($this->classMap[$className])) {
                    $newClassId = count($this->classMap);
                    $this->classMap[$className] = [
                        'id' => $newClassId,
                        'name' => $annotation['class']['name'],
                    ];
                }

                $mappedAnnotation = match ($annotationTechnique) {
                    AppConfig::ANNOTATION_TECHNIQUES['POLYGON'] => $this->mapPolygon($annotation),
                    AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'] => $this->mapBbox($annotation),
                    default => throw new \Exception("Invalid annotation technique"),
                };

                if (!Storage::append($annotationPath, $mappedAnnotation)) {
                    throw new \Exception("Failed to write annotation to file");
                }
            }
        }
    }


    abstract protected function getImageDestinationPath($datasetFolder, $image): string;

    abstract protected function getAnnotationDestinationPath($datasetFolder, $image): string;

    abstract protected function mapPolygon($annotation): string;

    abstract protected function mapBbox($annotation): string;
}
