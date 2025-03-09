<?php

namespace App\ImportService\Validators\Coco;

use App\Configs\Annotations\CocoConfig;
use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\ImportService\Validators\BaseValidator\BaseZipValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class CocoZipValidator extends BaseZipValidator
{
    public function validate(string $fileName): Response
    {
        try {
            $filePath = AppConfig::LIVEWIRE_TMP_PATH . $fileName;
            if (!Storage::exists($filePath)) {
                return Response::error("Zip file not found");
            }

            $resultImages = $this->validateImageFolder($filePath, CocoConfig::IMAGE_FOLDER);
            $resultAnnotation = $this->validateAnnotationFile($filePath);

            // Collect all errors in an array
            $errors = [];
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

    private function validateAnnotationFile(string $filePath)
    {
        $labelsFile = $filePath . '/' . CocoConfig::LABELS_FILE;
        if (!Storage::exists($labelsFile)) {
            return "Annotations file not found";
        }
        return true;
    }
}
