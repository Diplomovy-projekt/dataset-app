<?php

namespace App\ImportService\Validators\Yolo;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\ImportService\Validators\BaseValidator\BaseAnnotationValidator;
use App\Utils\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YoloAnnotationValidator extends BaseAnnotationValidator
{
    public function validate(string $datasetFolder, string $annotationTechnique): Response
    {
        $annotationsPath = AppConfig::LIVEWIRE_TMP_PATH . $datasetFolder .'/'. YoloConfig::LABELS_FOLDER;
        $labels = collect(Storage::files($annotationsPath));
        $errors = [];

        foreach ($labels as $labelFile) {
            try {
                $content = Storage::get($labelFile);
                $prettyLabelFile = pathinfo($labelFile, PATHINFO_BASENAME);
                $lines = array_filter(explode("\n", $content), 'strlen'); // Remove empty lines

                if (empty($lines)) {
                    $errors[$labelFile][] = "File is empty";
                    continue;
                }

                foreach ($lines as $lineNumber => $line) {
                    $lineNumber++; // Make line numbers 1-based
                    $parts = preg_split('/\s+/', trim($line));

                    // Validate based on format
                    if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                        // Check if segmentation is actually leveled rectangle and can be converted to bounding box
                        $bbox = $this->isLeveledRectangle(array_slice($parts, 1));
                        if($bbox){
                            $parts = array_merge([$parts[0]], $bbox);
                            $content = str_replace($line, implode(' ', $parts), $content);
                            continue;
                        }
                        // Bounding box must have exactly 5 parts
                        if (count($parts) !== 5) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Bounding box format requires exactly 5 values " .
                                "(class_id, x_center, y_center, width, height). Got " . count($parts);
                            continue;
                        }
                    } elseif ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                        // Polygon must have odd number of parts (class_id + coordinate pairs)
                        if (count($parts) < 7 || (count($parts) - 1) % 2 !== 0) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Polygon format requires uneven number of numbers(including class_id) and at least 3 coordinates. Got " . count($parts) . " values";
                            continue;
                        }
                    }
                    else {
                        $errors[$prettyLabelFile][] = "Unknown annotation technique: {$annotationTechnique}";
                        continue;
                    }

                    // Validate class_id is non-negative integer
                    if (!is_numeric($parts[0]) || strpos($parts[0], '.') !== false || $parts[0] < 0) {
                        $errors[$prettyLabelFile][] = "Line {$lineNumber}: Class ID must be a non-negative integer";
                        continue;
                    }

                    // Remove class_id for coordinate validation
                    $coordinates = array_slice($parts, 1);
                    foreach ($coordinates as $coord) {
                        $floatCoord = round((float)$coord, 3);
                        if ($floatCoord < 0 || $floatCoord > 1) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Coordinate {$coord} must be between 0 and 1";
                        }
                    }
                }
                Storage::put($labelFile, $content);
            } catch (\Exception $e) {
                return Response::error("An unexpected error occurred: " . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            return Response::error("Annotation issues found", $errors);
        }
        return Response::success();
    }

    private function isLeveledRectangle(array $points) {
        if (count($points) != 8) {
            return false;
        }

        // Split points into pairs of (x, y)
        $topLeft = [$points[0], $points[1]];
        $topRight = [$points[2], $points[3]];
        $bottomRight = [$points[4], $points[5]];
        $bottomLeft = [$points[6], $points[7]];

        // Check if top-left and top-right points have the same y-coordinate (top edge must be horizontal)
        if ($topLeft[1] != $topRight[1]) {
            return false;
        }

        // Check if bottom-left and bottom-right points have the same y-coordinate (bottom edge must be horizontal)
        if ($bottomLeft[1] !== $bottomRight[1]) {
            return false;
        }

        // Check if top-left and bottom-left points have the same x-coordinate (left edge must be vertical)
        if ($topLeft[0] != $bottomLeft[0]) {
            return false;
        }

        // Check if top-right and bottom-right points have the same x-coordinate (right edge must be vertical)
        if ($topRight[0] != $bottomRight[0]) {
            return false;
        }

        // Check if opposite sides are of equal length
        $topEdgeLength = $this->distanceBetweenPoints($topLeft, $topRight);
        $bottomEdgeLength = $this->distanceBetweenPoints($bottomLeft, $bottomRight);
        $leftEdgeLength = $this->distanceBetweenPoints($topLeft, $bottomLeft);
        $rightEdgeLength = $this->distanceBetweenPoints($topRight, $bottomRight);

        // Ensure opposite sides are equal in length
        if ($topEdgeLength !== $bottomEdgeLength || $leftEdgeLength !== $rightEdgeLength) {
            return false;
        }

        return $this->transformToYoloBoundingBoxNormalized($points);
    }

    private function distanceBetweenPoints(array $point1, array $point2): float {
        return sqrt(pow($point2[0] - $point1[0], 2) + pow($point2[1] - $point1[1], 2));
    }

    private function transformToYoloBoundingBoxNormalized(array $points): array {
        // Split points into pairs of (x, y)
        $topLeft = [$points[0], $points[1]];
        $topRight = [$points[2], $points[3]];
        $bottomRight = [$points[4], $points[5]];
        $bottomLeft = [$points[6], $points[7]];

        // Calculate the center coordinates (x_center, y_center)
        $x_center = ($topLeft[0] + $topRight[0] + $bottomLeft[0] + $bottomRight[0]) / 4;
        $y_center = ($topLeft[1] + $topRight[1] + $bottomLeft[1] + $bottomRight[1]) / 4;

        // Calculate width and height
        $width = $this->distanceBetweenPoints($topLeft, $topRight); // or between bottom-left and bottom-right
        $height = $this->distanceBetweenPoints($topLeft, $bottomLeft); // or between top-right and bottom-right

        return [$x_center, $y_center, $width, $height];
    }
}
