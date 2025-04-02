<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\BaseAnnotationConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ExportService\Interfaces\ToMapperInterface;
use App\Models\Dataset;
use App\Utils\Util;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class BaseToMapper
{
    protected static string $configClass = BaseAnnotationConfig::class;
    protected array $classMap = [];

    /**
     * Entrypoint of Export process. This method will call the necessary methods to handle the export process.
     * If a derived class needs to perform additional operations, it can override the customHandle method.
     * This method is called for every chunk of images.
     *
     * @param array $images The images to be processed. This is a chunk of images with annotations and classes.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the handling process.
     */
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
     * Create symbolic links for images to folder that will be later zipped and downloaded.
     * The images will be linked to the folder that is defined in the format config. IMAGE_FOLDER.
     *
     * @param array $images The images to be processed.
     * @throws Exception
     */
    public function linkImages($images, $datasetFolder): void
    {
        $env = App::environment();
        $datasets = Dataset::whereIn('id', array_column($images, 'dataset_id'))->get()->keyBy('id');
        $destinationDir = $this->getImageDestinationDir($datasetFolder);
        File::ensureDirectoryExists($destinationDir);

        foreach ($images as $image) {
            $dataset = $datasets[$image['dataset_id']];
            $source = Util::getDatasetPath($dataset, true) . 'full-images/' . $image['filename'];
            $destination = $destinationDir . '/' . $image['filename'];

            if (is_link($destination) || File::exists($destination)) {
                continue;
            }

            // Create symbolic link. On local windows, we need to copy the file instead of linking.
            if (File::exists($source)) {
                if ($env == 'local') {
                    if (File::link($source, $destination)) {
                        throw new Exception("Failed to symlink image... \nFrom: $source \nTo: $destination");
                    }
                } else {
                    if (!File::link($source, $destination)) {
                        throw new Exception("Failed to link image... \nFrom: $source \nTo: $destination");
                    }
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
     * @throws Exception
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
     * Get absolute path where to symlink Image, (absolute because uses File facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @return string The destination path for the image.
     */
    public function getImageDestinationDir($datasetFolder): string
    {
        $imageFolder = static::$configClass::IMAGE_FOLDER;
        $path = AppConfig::DATASETS_PATH['public'] . $datasetFolder;

        if ($imageFolder) {
            $path .= '/' . $imageFolder;
        }

        return Storage::path($path);
    }

    /**
     * This is the main method in the child classes that will be responsible for creating and saving the annotations.
     * Parse image(if format needs it) and annotation data to map them to the selected format and save them in the dataset folder.
     *
     * @param array $images The images to be processed. This is a chunk of images with annotations and classes.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the annotation creation process.
     */
    abstract public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void;

    /**
     * Get relative path to folder where annotation will be created, (relative to the storage folder, because uses Storage facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @param array|null $image The image data.
     * @return string The destination path for the image.
     */
    abstract public function getAnnotationDestinationPath(string $datasetFolder, array $image = null): string;

    /**
     * Maps a polygon annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @param array|null $imgDims The image dimensions.
     * @return mixed The mapped polygon annotation.
     */
    abstract public function mapPolygon(mixed $annotation, array $imgDims = null): mixed;

    /**
     * Maps a bounding box annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @param array|null $imgDims The image dimensions.
     * @return mixed The mapped bounding box annotation.
     */
    abstract public function mapBbox(mixed $annotation, array $imgDims = null): mixed;
}
