<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\BaseAnnotationConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
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
                if ($env == 'local' or $env == 'testing') {
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
     * This is the main method in the child classes that will be responsible for creating and saving the annotations to file.
     * Parse image (if format needs it) and annotation data to map them to the selected format and save them in the dataset folder.
     *
     * This function receives the images in chunks. Each chunk should be processed, saved, and then the next chunk should be handled.
     * It's important to note that the same image may appear in multiple chunks with different annotation data,
     * so the function should be able to handle this and ensure that annotations are correctly applied for each image.
     *
     * @param array $images The images to be processed. This is a chunk of images with annotations and classes.
     * Each image is an associative array with the following structure:
     * [
     *     0 => [
     *         "id" => int,               // Image identifier (e.g., 5069)
     *         "filename" => string,      // The filename of the image (e.g., "DSC_0930_JPG.rf.542c66d30983c627322c4764a8710b9c_da_67deb2873ed47_da_67eb0962e8762.jpg")
     *         "dataset_folder" => string, // Identifier for the dataset folder (e.g., "0195ee1c9d9f-0073-7328-bada-e7c938f0e20816ed20be")
     *         "dataset_id" => int,       // ID of the dataset (e.g., 109)
     *         "width" => int,            // Image width in pixels (e.g., 4928)
     *         "height" => int,           // Image height in pixels (e.g., 3264)
     *         "annotations" => [         // Annotations for the image
     *             0 => [
     *                 "id" => int,                      // Annotation ID (e.g., 244646)
     *                 "image_id" => int,                // Associated image ID (e.g., 5069)
     *                 "x" => float,                     // X coordinate of the annotation (e.g., 0.15422077922078)
     *                 "y" => float,                     // Y coordinate of the annotation (e.g., 0.3921568627451)
     *                 "width" => float,                 // Width of the annotation (e.g., 0.44237012987013)
     *                 "height" => float,                // Height of the annotation (e.g., 0.57138480392157)
     *                 "annotation_class_id" => int,     // ID of the annotation class (e.g., 506)
     *                 "segmentation" => [               // Segmentation points (if applicable)
     *                     0 => ["x" => float, "y" => float], // Example: ["x" => 0.5671672077922078, "y" => 0.39215686274509803]
     *                     1 => ["x" => float, "y" => float], // Additional points may follow
     *                 ],
     *                 "class" => [                      // Class details
     *                     "id" => int,                   // Class ID (e.g., 506)
     *                     "name" => string,               // Class name (e.g., "dog")
     *                     "rgb" => string,                // RGB color representation (e.g., "rgb(236, 54, 1)")
     *                 ]
     *             ],
     *             1 => [
     *                 // Another annotation data
     *             ],
     *         ],
     *     ],
     *     1 => [
     *         // Another image data
     *     ],
     *     2 => [
     *         // Another image data
     *     ],
     * ]
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
