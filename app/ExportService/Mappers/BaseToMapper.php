<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ExportService\Interfaces\ToMapperInterface;
use App\Models\Dataset;
use App\Utils\Util;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class BaseToMapper implements ToMapperInterface
{
    protected array $classMap = [];

    public function handle(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        $this->linkImages($images, $datasetFolder);
        $this->createAnnotations($images, $datasetFolder, $annotationTechnique);
        $this->customHandle($datasetFolder);
    }

    protected function customHandle(string $datasetFolder): void
    {
        // No-op, only overridden when needed
    }
    /**
     * @throws Exception
     */
    public function linkImages($images, $datasetFolder): void
    {
        $datasets = Dataset::whereIn('id', array_column($images, 'dataset_id'))->get()->keyBy('id');
        $destinationDir = $this->getImageDestinationDir($datasetFolder);

        foreach ($images as $image) {
            $dataset = $datasets[$image['dataset_id']];
            $source = Util::getDatasetPath($dataset, true) . 'full-images/' . $image['filename'];

            File::ensureDirectoryExists($destinationDir);

            // Create symbolic link
            if (File::exists($source)) {
                $destination = $destinationDir . '/' . $image['filename'];
                if (!File::link($source, $destination)) {
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

    public function getImageDestinationDir($datasetFolder): string
    {
        $imageFolder = $this->getImageFolder();
        $path = AppConfig::DATASETS_PATH['public'] . $datasetFolder . '/' . $imageFolder;
        return Storage::path($path);
    }

    abstract public function getImageFolder(): string;
}
