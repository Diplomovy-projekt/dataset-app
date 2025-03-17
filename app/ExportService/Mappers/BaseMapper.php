<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Utils\Util;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class BaseMapper
{
    protected array $classMap = [];


    /**
     * @throws Exception
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
                    throw new Exception("Failed to link image... \nFrom: $source \nTo: $destination");
                }
            } else {
                throw new Exception("Image not found: $source");
            }
        }
    }
    /**
     * Create new class map with new ids.
     */
    protected function mapClass($className): void
    {
        if (!isset($this->classMap[$className])) {
            $newClassId = count($this->classMap);
            $this->classMap[$className] = [
                'id' => $newClassId,
                'name' => $className,
            ];
        }
    }
    protected function getClassId($className): int
    {
        return $this->classMap[$className]['id'];
    }

    /**
     * Map the annotation based on the selected technique (POLYGON or BOUNDING_BOX).
     * This method can be overridden in derived classes for specific formats.
     */
    protected function mapAnnotation($annotationTechnique, $annotation, array $imgDims = null): mixed
    {
        return match ($annotationTechnique) {
            AppConfig::ANNOTATION_TECHNIQUES['POLYGON'] => $this->mapPolygon($annotation, $imgDims),
            AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'] => $this->mapBbox($annotation,  $imgDims),
            default => throw new Exception("Invalid annotation technique"),
        };
    }


    /**
     * Starting point of export processing
     * Handles the mapping process for the given images, dataset folder, and annotation technique.
     * Should call the linkImages and mapAnnotations methods.
     * Optionally can call other methods specific to the format.
     *
     * @param array $images The images to be processed.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the handling process.
     */
    abstract public function handle(array $images, string $datasetFolder, string $annotationTechnique): void;

    /**
     * Create annotation file/files for the dataset.
     *
     * @param array $images The images to be processed.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the annotation creation process.
     */
    abstract protected function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void;

    /**
     * Get absolute path where to symlink Image, (absolute because uses File facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @param array $image The image data.
     * @return string The destination path for the image.
     */
    abstract protected function getImageDestinationPath(string $datasetFolder, array $image): string;

    /**
     * Get relative path where annotation will be created, (relative because uses Storage facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @param array|null $image The image data.
     * @return string The destination path for the image.
     */
    abstract protected function getAnnotationDestinationPath(string $datasetFolder, array $image = null): string;

    /**
     * Maps a polygon annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @return string The mapped polygon annotation in YOLO format.
     */
    abstract protected function mapPolygon(mixed $annotation, array $imgDims = null): mixed;

    /**
     * Maps a bounding box annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @return string The mapped polygon annotation in YOLO format.
     */
    abstract protected function mapBbox(mixed $annotation, array $imgDims = null): mixed;
}
