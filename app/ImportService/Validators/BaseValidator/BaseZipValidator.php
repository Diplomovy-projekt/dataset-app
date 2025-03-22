<?php

namespace App\ImportService\Validators\BaseValidator;

use App\Configs\Annotations\BaseAnnotationConfig;
use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ImportService\Interfaces\ZipValidatorInterface;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

abstract class BaseZipValidator
{
    const array IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    protected static string $configClass = BaseAnnotationConfig::class;

    /**
     * Validate the folder structure.
     *
     * @param string $folderName The name of the folder.
     * @throws DataException
     * @throws \Exception
     */
    public function validateStructure(string $folderName): void
    {
        $datasetPath = $this->getPath($folderName);
        $this->validateImageOrganization($datasetPath, static::$configClass::IMAGE_FOLDER);
        $this->validateAnnotationOrganization($datasetPath, static::$configClass::LABEL_EXTENSION, static::$configClass::LABELS_FOLDER);
        $this->additionalStructureValidation($datasetPath);
    }

    /**
     * Validates the contents of a folder by ensuring all files have allowed extensions.
     *
     * @param string       $folderPath       The path to the folder to validate.
     * @param array|string $allowedExtensions A list of allowed file extensions.
     *
     * @throws DataException If invalid files are found or the folder does not exist.
     */
    protected function validateFolderContent(string $folderPath, array|string $allowedExtensions): void
    {
        if (!Storage::exists($folderPath)) {
            throw new DataException("Folder not found: $folderPath");
        }

        $files = Storage::files($folderPath);
        $invalidFiles = array_filter($files, fn($file) => !in_array(pathinfo($file, PATHINFO_EXTENSION), (array) $allowedExtensions));

        if (!empty($invalidFiles)) {
            throw new DataException("Invalid files found in $folderPath", $invalidFiles);
        }
    }

    /**
     * Get relative path from storage folder to the dataset.
     *
     * @param string $folderName The name of the folder.
     * @return string The full path to the folder.
     * @throws \Exception If the folder does not exist.
     */
    protected function getPath(string $folderName): string
    {
        $path = AppConfig::LIVEWIRE_TMP_PATH . $folderName;
        if (!Storage::exists($path)) {
            throw new \Exception("Zip file not found");
        }
        return $path;
    }

    /**
     * Hook method for additional structure validation.
     * Child classes can override this if needed.
     *
     * @param string $datasetPath The path to the folder.
     * @throws DataException
     */
    protected function additionalStructureValidation(string $datasetPath): void
    {
        // Default implementation does nothing.
    }

    /**
     * Validates the organization of images within the dataset.
     *
     * @param string $datasetPath The root path of the dataset.
     * @param string $imageFolder The folder where images are stored.
     *
     * @throws DataException If image validation fails.
     */
    abstract protected function validateImageOrganization(string $datasetPath, ?string $imageFolder): void;

    /**
     * Validates the organization of annotations within the dataset.
     *
     * @param string      $datasetPath      The root path of the dataset.
     * @param string      $labelExtension   The expected label file extension(s).
     * @param string|null $annotationFolder The folder where annotations are stored (if applicable).
     *
     * @throws DataException If annotation validation fails.
     */
    abstract protected function validateAnnotationOrganization(string $datasetPath, string $labelExtension, ?string $annotationFolder = null): void;
}
