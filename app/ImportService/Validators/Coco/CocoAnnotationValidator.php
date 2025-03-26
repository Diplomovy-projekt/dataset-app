<?php

namespace App\ImportService\Validators\Coco;

use App\Configs\Annotations\CocoConfig;
use App\Configs\AppConfig;
use App\ImportService\Validators\BaseValidator\BaseAnnotationValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class CocoAnnotationValidator extends BaseAnnotationValidator
{
    public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response
    {
        // Get the COCO annotations file
        $annotationPath = $this->getAnnotationPath($datasetFolder, CocoConfig::LABELS_FILE);
        $cocoJson = Storage::get($annotationPath);
        $cocoJson = json_decode($cocoJson, true);

        $errors = [];
        // Check if basic keys exist
        if (!isset($cocoJson['images'], $cocoJson['annotations'], $cocoJson['categories'])) {
            $errors['missingKeys'] = "Missing required keys in the COCO JSON file.";
            return Response::error("Invalid COCO JSON file.", $errors);
        }

        $images = $cocoJson['images'];
        $annotations = $cocoJson['annotations'];
        $categories = $cocoJson['categories'];

        // Validate images structure
        foreach ($images as $index => $image) {
            if (!isset($image['id'], $image['file_name'], $image['width'], $image['height'])) {
                $errors['image index: '.$index][] = "Invalid image entry: Missing required fields.";
            }
        }

        // Validate annotations structure and segmentation if polygon technique
        foreach ($annotations as $index => $annotation) {
            if (!isset($annotation['image_id'], $annotation['category_id'], $annotation['bbox']) ||
                !is_array($annotation['bbox']) || count($annotation['bbox']) !== 4) {
                $errors['annotation index: '.$index][] = "Invalid annotation entry: Missing required fields or invalid bounding box.";
                continue;
            }

            // Validate segmentation if polygon technique is used
            if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON'] && (empty($annotation['segmentation'][0]))) {
                $errors[$index][] = "Invalid annotation entry: Missing segmentation data.";
            }
        }

        // Validate categories structure
        foreach ($categories as $category) {
            if (!isset($category['id'], $category['name'])) {
                $errors['category'][] = "Invalid category entry: Missing required fields.";
            }
        }

        return empty($errors)
            ? Response::success()
            : Response::error("Invalid COCO JSON file.", $errors);
    }
}
