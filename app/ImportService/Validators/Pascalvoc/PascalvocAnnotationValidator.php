<?php

namespace App\ImportService\Validators\Pascalvoc;

use App\Configs\Annotations\PascalvocConfig;
use App\Configs\AppConfig;
use App\ImportService\Validators\BaseValidator\BaseAnnotationValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class PascalvocAnnotationValidator extends BaseAnnotationValidator
{
    /**
     * @throws \Exception
     */
    public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response
    {
        $annotationPath = $this->getAnnotationPath($datasetFolder, PascalvocConfig::LABELS_FOLDER);

        $xmlFiles = collect(Storage::files($annotationPath))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === PascalvocConfig::LABEL_EXTENSION)
            ->values();
        $errors = [];

        foreach ($xmlFiles as $file) {
            $fileErrors = $this->validateSingleAnnotation($file, $annotationTechnique);

            if (!empty($fileErrors)) {
                $errors[basename($file)] = $fileErrors;
            }
        }

        if (!empty($errors)) {
            return Response::error(
                "Found " . count($errors) . " invalid annotation files out of " . count($xmlFiles),
                $errors
            );
        }

        return Response::success();
    }

    private function validateSingleAnnotation(string $filePath, string $annotationTechnique): array
    {
        $fileErrors = [];

        if (!Storage::exists($filePath)) {
            $fileErrors[] = "File does not exist";
            return $fileErrors;
        }

        $content = Storage::get($filePath);

        // Validate XML format
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);

        // Check required fields
        $requiredFields = ['folder', 'filename', 'size', 'object'];
        foreach ($requiredFields as $field) {
            if (!isset($xml->{$field})) {
                $fileErrors[] = "Missing required field: $field";
            }
        }

        // Validate image dimensions
        if (isset($xml->size)) {
            $sizeFields = ['width', 'height', 'depth'];
            foreach ($sizeFields as $field) {
                if (!isset($xml->size->{$field})) {
                    $fileErrors[] = "Missing size field: $field";
                } else if (!is_numeric((string)$xml->size->{$field}) || (int)$xml->size->{$field} <= 0) {
                    $fileErrors[] = "Invalid size value for $field: " . (string)$xml->size->{$field};
                }
            }
        }

        // Validate objects
        if (!isset($xml->object)) {
            $fileErrors[] = "Missing objects array";
        } else if (count($xml->object) === 0) {
            $fileErrors[] = "No objects found in annotation";
        } else {
            // Validate each object based on annotation technique
            foreach ($xml->object as $index => $object) {
                $objectErrors = $this->validateObject($object, $annotationTechnique);
                if (!empty($objectErrors)) {
                    $fileErrors["object_$index"] = $objectErrors;
                }
            }
        }

        // Check if referenced image exists
        if (isset($xml->filename)) {
            $baseDir = dirname($filePath); // Move up from 'annotations/' to the parent folder
            $imagePath = $baseDir . '/' . (string)$xml->filename;

            if (!Storage::exists($imagePath)) {
                $fileErrors[] = "Referenced image does not exist in 'images' folder: " . (string)$xml->filename;
            }
        }

        return $fileErrors;
    }

    private function validateObject($object, string $annotationTechnique): array
    {
        $objectErrors = [];

        // Check required object fields
        $requiredObjectFields = ['name', 'bndbox'];
        foreach ($requiredObjectFields as $field) {
            if (!isset($object->{$field})) {
                $objectErrors[] = "Missing required field: $field";
            }
        }

        // Validate shape type based on annotation technique
        $isBoundingBox = $annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'];
        $isPolygon = $annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON'];

        // For polygon annotations, check if polygon points exist
        if ($isPolygon && (!isset($object->polygon) || count($object->polygon) === 0)) {
            $objectErrors[] = "Missing polygon points for polygon annotation technique";
        }

        // Validate bounding box coordinates
        if (isset($object->bndbox)) {
            $bboxFields = ['xmin', 'ymin', 'xmax', 'ymax'];
            foreach ($bboxFields as $field) {
                if (!isset($object->bndbox->{$field})) {
                    $objectErrors[] = "Missing bounding box coordinate: $field";
                } else if (!is_numeric((string)$object->bndbox->{$field})) {
                    $objectErrors[] = "Invalid coordinate value for $field: " . (string)$object->bndbox->{$field};
                }
            }

            // Check if coordinates are valid (xmax > xmin and ymax > ymin)
            if (isset($object->bndbox->xmin, $object->bndbox->xmax, $object->bndbox->ymin, $object->bndbox->ymax)) {
                $xmin = (float)$object->bndbox->xmin;
                $xmax = (float)$object->bndbox->xmax;
                $ymin = (float)$object->bndbox->ymin;
                $ymax = (float)$object->bndbox->ymax;

                if ($xmin >= $xmax) {
                    $objectErrors[] = "Invalid bounding box: xmin ($xmin) must be less than xmax ($xmax)";
                }
                if ($ymin >= $ymax) {
                    $objectErrors[] = "Invalid bounding box: ymin ($ymin) must be less than ymax ($ymax)";
                }
            }
        }

        // Validate polygon coordinates for polygon annotation
        if ($isPolygon && isset($object->polygon)) {
            $points = (array) $object->polygon; // Ensure it's an array

            if (count($points) < 6) { // At least 3 points => 6 values (x1, y1, x2, y2, x3, y3)
                $objectErrors[] = "Polygon must have at least 3 points, found: " . count($points) / 2;
            }

            // Validate each coordinate pair
            foreach ($points as $key => $value) {
                if (!is_numeric($value)) {
                    $objectErrors[] = "Invalid coordinate value for key '$key': $value";
                }
            }
        }


        return $objectErrors;
    }
}
