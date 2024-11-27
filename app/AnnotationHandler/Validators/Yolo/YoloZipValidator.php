<?php

namespace App\AnnotationHandler\Validators\Yolo;

use App\AnnotationHandler\ImportHandlers\Yolo\YoloImportHandler;
use App\AnnotationHandler\traits\Yolo\YoloFormatTrait;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class YoloZipValidator
{
    use YoloFormatTrait;
    public function validate(string $zipPath): array
    {
        try {
            if (!Storage::exists($zipPath)) {
                return ["error" => "File not found"];
            }

            $resultDataFile = $this->validateDataFile($zipPath);
            $resultImages = $this->validateImageFolder($zipPath . '/' . self::IMAGE_FOLDER);
            $resultAnnotation = $this->validateAnnotationFolder($zipPath . '/' . self::LABELS_FOLDER);

            // Collect all errors in an array
            $errors = [];

            if ($resultDataFile !== true) {
                $errors["dataFile"] = $resultDataFile;
            }

            if ($resultImages !== true) {
                $errors["images"] = $resultImages;
            }

            if ($resultAnnotation !== true) {
                $errors["annotations"] = $resultAnnotation;
            }

            // Return the errors if any
            return $errors;

        } catch (\Exception $e) {
            return ["error" => "An unexpected error occurred: " . $e->getMessage()];
        }
    }

    private function validateDataFile(string $archiveDir)
    {
        $dataFilePath = $archiveDir . '/' . self::DATA_YAML;
        if (!Storage::exists($dataFilePath)) {
            return "Data.yaml not found";
        }
        // Read and parse the YAML file
        $dataContent = Storage::get($dataFilePath);
        $annotationData = Yaml::parse($dataContent);
        // check if 'nc' and 'names' keys are present
        if (!isset($annotationData['nc']) || !isset($annotationData['names'])) {
            return "Missing 'nc' or 'names' key in data.yaml";
        }

        return true;
    }

    private function validateImageFolder(string $imagesPath)
    {
        $images = collect(Storage::files($imagesPath));

        // Filter out invalid image files
        $invalidImages = $images->filter(function ($image) {
            return !in_array(pathinfo($image, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']);
        });

        if ($invalidImages->isNotEmpty()) {
            return ["Invalid image files found" => $invalidImages->toArray()];
        }

        return true;
    }

    private function validateAnnotationFolder(string $annotationsPath)
    {
        $labels = collect(Storage::disk('local')->files($annotationsPath));

        // Filter out invalid label files
        $invalidLabels = $labels->filter(function ($label) {
            return !in_array(pathinfo($label, PATHINFO_EXTENSION), (array)self::TXT_EXTENSION);
        });

        if ($invalidLabels->isNotEmpty()) {
            return ["Invalid label files found" => $invalidLabels->toArray()];
        }

        return true;
    }



}
