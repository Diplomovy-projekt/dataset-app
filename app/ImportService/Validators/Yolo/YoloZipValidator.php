<?php

namespace App\ImportService\Validators\Yolo;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class YoloZipValidator
{
    public function validate(string $fileName): Response
    {
        try {
            $filePath = AppConfig::LIVEWIRE_TMP_PATH . $fileName;
            if (!Storage::exists($filePath)) {
                return Response::error("Zip file not found");
            }

            $resultDataFile = $this->validateDataFile($filePath);
            $resultImages = $this->validateImageFolder($filePath);
            $resultAnnotation = $this->validateAnnotationFolder($filePath);

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

            if (!empty($errors)) {
                return Response::error("Zip structure issues found", $errors);
            }
            return Response::success();
        } catch (\Exception $e) {
            return Response::error("Unexpected error occurred during zip structure validation",$e->getMessage());
        }
    }

    private function validateDataFile(string $filePath)
    {
        $dataFilePath = $filePath . '/' . YoloConfig::DATA_YAML;
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

    private function validateImageFolder(string $filePath)
    {
        $imagesPath = $filePath . '/' . YoloConfig::IMAGE_FOLDER;
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

    private function validateAnnotationFolder(string $filePath)
    {
        $labelsPath = $filePath . '/' . YoloConfig::LABELS_FOLDER;
        $labels = collect(Storage::files($labelsPath));

        // Filter out invalid label files
        $invalidLabels = $labels->filter(function ($label) {
            return !in_array(pathinfo($label, PATHINFO_EXTENSION), (array)YoloConfig::LABEL_EXTENSION);
        });

        if ($invalidLabels->isNotEmpty()) {
            return ["Invalid label files found" => $invalidLabels->toArray()];
        }

        return true;
    }



}
