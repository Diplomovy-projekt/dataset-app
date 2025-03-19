<?php

namespace App\ExportService\Interfaces;

use Exception;

interface ToMapperInterface
{
    /**
     * Handles the mapping process for the given images, dataset folder, and annotation technique.
     * Should call the linkImages and mapAnnotations methods.
     * Optionally can call other methods specific to the format.
     *
     * @param array $images The images to be processed.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the handling process.
     */
    public function handle(array $images, string $datasetFolder, string $annotationTechnique): void;

    /**
     * Create annotation file/files for the dataset.
     *
     * @param array $images The images to be processed.
     * @param string $datasetFolder The folder of the dataset.
     * @param string $annotationTechnique The technique used for annotation.
     * @throws Exception If an error occurs during the annotation creation process.
     */
    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void;

    /**
     * Get absolute path where to symlink Image, (absolute because uses File facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @return string The destination path for the image.
     */
    public function getImageDestinationDir(string $datasetFolder): string;

    /**
     * Get relative path where annotation will be created, (relative because uses Storage facade)
     *
     * @param string $datasetFolder The folder of the dataset.
     * @param array|null $image The image data.
     * @return string The destination path for the image.
     */
    public function getAnnotationDestinationPath(string $datasetFolder, array $image = null): string;

    /**
     * Maps a polygon annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @param array|null $imgDims The image dimensions.
     * @return mixed The mapped polygon annotation.
     */
    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed;

    /**
     * Maps a bounding box annotation from internal format to selected format.
     *
     * @param mixed $annotation The annotation data.
     * @param array|null $imgDims The image dimensions.
     * @return mixed The mapped bounding box annotation.
     */
    public function mapBbox(mixed $annotation, array $imgDims = null): mixed;
}
