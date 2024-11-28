<?php

namespace App\AnnotationHandler\Validators\Yolo;

use App\AnnotationHandler\traits\Yolo\YoloFormatTrait;
use App\Utils\AppConstants;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YoloAnnotationValidator
{
    use YoloFormatTrait;

    public function validate(string $fileName, string $annotationTechnique): array
    {
        $annotationsPath = AppConstants::LIVEWIRE_TMP_PATH . $fileName .'/'. self::LABELS_FOLDER;
        $labels = collect(Storage::files($annotationsPath));
        $errors = [];

        // Start measuring time
        $startTime = microtime(true);

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
                    if ($annotationTechnique === AppConstants::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                        // Bounding box must have exactly 5 parts
                        if (count($parts) !== 5) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Bounding box format requires exactly 5 values " .
                                "(class_id, x_center, y_center, width, height). Got " . count($parts);
                            continue;
                        }
                    } else {
                        // Polygon must have odd number of parts (class_id + coordinate pairs)
                        if (count($parts) < 7 || (count($parts) - 1) % 2 !== 0) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Polygon format requires uneven number of numbers(including class_id) and at least 3 coordinates. Got " . count($parts) . " values";
                            continue;
                        }
                    }

                    // Validate class_id is non-negative integer
                    if (!is_numeric($parts[0]) || strpos($parts[0], '.') !== false || $parts[0] < 0) {
                        $errors[$prettyLabelFile][] = "Line {$lineNumber}: Class ID must be a non-negative integer";
                        continue;
                    }

                    // Remove class_id for coordinate validation
                    $coordinates = array_slice($parts, 1);
                    foreach ($coordinates as $coord) {
                        $floatCoord = (float)$coord;
                        if ($floatCoord < 0 || $floatCoord > 1) {
                            $errors[$prettyLabelFile][] = "Line {$lineNumber}: Coordinate {$coord} must be between 0 and 1";
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[$labelFile][] = "Error processing file: " . $e->getMessage();
            }
        }

        // Measure time taken
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Optionally log the execution time for debugging or performance tracking
        Log::info("validateAnnotationFormat execution time: " . $executionTime . " seconds");

        return $errors;
    }
}
