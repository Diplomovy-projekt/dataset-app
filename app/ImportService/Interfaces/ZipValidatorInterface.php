<?php

namespace App\ImportService\Interfaces;

use App\Utils\Response;

interface ZipValidatorInterface
{
    /**
     * Validates the image folder by checking for unsupported file types.
     *
     * @param string $filePath The base file path.
     * @param string $imageFolder The folder containing images.
     * @return void Returns true if valid, otherwise an array of invalid files.
     */
    public function validateImageOrganization(string $filePath, string $imageFolder): void;

    /**
     * Validates the annotation folder structure and organization.
     *
     * @param string $filePath The base file path where annotations are stored.
     * @param string|null $annotationFolder The optional folder containing annotations.
     * @return void Returns true if valid, otherwise false.
     */
    public function validateAnnotationOrganization(string $filePath, string $labelExtension, ?string $annotationFolder = null): void;

    /**
     * Validates the structure of the dataset zip file.
     * Ensures the presence of required format-specific files and folders.
     *
     * @param string $folderName The base folder name containing the dataset.
     * @return void Returns true if the structure is valid, otherwise false.
     */
    public function validateStructure(string $folderName): void;
}
