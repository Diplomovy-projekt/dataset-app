<?php

namespace App\ImportService\Validators\Labelme;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\AppConfig;
use App\ImportService\Validators\BaseValidator\BaseAnnotationValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class LabelmeAnnotationValidator extends BaseAnnotationValidator
{
    /**
     * @throws \Exception
     */
    public function validateAnnotationData(string $datasetFolder, string $annotationTechnique): Response
    {
        $annotationPath = $this->getAnnotationPath($datasetFolder, LabelmeConfig::LABELS_FOLDER);

        $jsonFiles = Storage::files($annotationPath);
        $errors = [];

        foreach ($jsonFiles as $file) {
            $fileErrors = $this->validateSingleAnnotation($file, $annotationTechnique);

            if (!empty($fileErrors)) {
                $errors[basename($file)] = $fileErrors;
            }
        }

        if (!empty($errors)) {
            return Response::error(
                "Found " . count($errors) . " invalid annotation files out of " . count($jsonFiles),
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
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $fileErrors[] = "Invalid JSON format: " . json_last_error_msg();
            return $fileErrors;
        }

        // Check required fields
        $requiredFields = ['version', 'shapes', 'imagePath', 'imageHeight', 'imageWidth'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $fileErrors[] = "Missing required field: $field";
            }
        }

        // Validate image dimensions
        if (isset($data['imageHeight'], $data['imageWidth'])) {
            if (!is_numeric($data['imageHeight']) || !is_numeric($data['imageWidth']) ||
                $data['imageHeight'] <= 0 || $data['imageWidth'] <= 0) {
                $fileErrors[] = "Invalid image dimensions";
            }
        }

        // Validate shapes
        if (!isset($data['shapes'])) {
            $fileErrors[] = "Missing shapes array";
        } else if (!is_array($data['shapes'])) {
            $fileErrors[] = "Shapes is not an array";
        } else if (empty($data['shapes'])) {
            $fileErrors[] = "No shapes found in annotation";
        } else {
            // Validate each shape based on annotation technique
            foreach ($data['shapes'] as $index => $shape) {
                $shapeErrors = $this->validateShape($shape, $annotationTechnique, $index);
                if (!empty($shapeErrors)) {
                    $fileErrors["shape_$index"] = $shapeErrors;
                }
            }
        }

        // Check if imagePath points to a valid image
        if (isset($data['imagePath'])) {
            $baseDir = dirname($filePath, 2); // Move up from 'labels/' to the parent folder
            $imagePath = $baseDir . '/' . LabelmeConfig::IMAGE_FOLDER . '/' . basename($data['imagePath']); // Ensure image is in 'images' folder

            if (!Storage::exists($imagePath)) {
                $fileErrors[] = "Referenced image does not exist in 'images' folder: {$data['imagePath']}";
            }
        }
        return $fileErrors;
    }

    private function validateShape(array $shape, string $annotationTechnique, int $index): array
    {
        $shapeErrors = [];
        $annotationTechnique = $annotationTechnique == AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'] ? 'rectangle' : 'polygon';
        // Check required shape fields
        $requiredShapeFields = ['label', 'points', 'shape_type', 'flags'];
        foreach ($requiredShapeFields as $field) {
            if (!isset($shape[$field])) {
                $shapeErrors[] = "Missing required field: $field";
            }
        }

        // Check if shape_type is valid (only polygon and rectangle)
        if (isset($shape['shape_type'])) {
            $validShapeTypes = [
                strtolower(AppConfig::ANNOTATION_TECHNIQUES['POLYGON']),
                'rectangle',
            ];

            if (!in_array($shape['shape_type'], $validShapeTypes)) {
                $shapeErrors[] = "Unsupported shape type: {$shape['shape_type']}. Only polygon and rectangle are supported";
            }

            // Check if shape_type matches annotation technique
            if ($shape['shape_type'] !== $annotationTechnique) {
                $shapeErrors[] = "Shape type ({$shape['shape_type']}) does not match annotation technique ($annotationTechnique)";
            }
        }

        // Validate points
        if (isset($shape['points'])) {
            if (!is_array($shape['points']) || empty($shape['points'])) {
                $shapeErrors[] = "No points defined";
            } else {
                // Points validation based on shape type
                if (isset($shape['shape_type'])) {
                    switch ($shape['shape_type']) {
                        case 'polygon':
                            if (count($shape['points']) < 3) {
                                $shapeErrors[] = "Polygon must have at least 3 points, found: " . count($shape['points']);
                            }
                            break;

                        case 'rectangle':
                            if (count($shape['points']) != 2) {
                                $shapeErrors[] = "Rectangle must have exactly 2 points, found: " . count($shape['points']);
                            }
                            break;
                    }

                    // Validate each point's format
                    foreach ($shape['points'] as $pointIndex => $point) {
                        if (!is_array($point) || count($point) != 2 ||
                            !is_numeric($point[0]) || !is_numeric($point[1])) {
                            $shapeErrors[] = "Invalid point at index $pointIndex";
                        }
                    }
                }
            }
        }

        return $shapeErrors;
    }
}
