<?php

namespace App\AnnotationHandler\Importers;

use App\AnnotationHandler\ImportHandlers\Yolo\YoloImportHandler;
use App\AnnotationHandler\Interfaces\ImporterInterface;
use App\AnnotationHandler\traits\Yolo\YoloFormatTrait;
use App\Utils\AppConstants;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class YoloImporter
{
    use YoloFormatTrait;
    public function parse(string $folderName, $annotationTechnique): array
    {
        // Define folder paths
        $datasetPath = AppConstants::LIVEWIRE_TMP_PATH . $folderName;
        $imageFolder = $datasetPath . '/' . self::IMAGE_FOLDER;
        $annotationFolder = $datasetPath . '/' . self::LABELS_FOLDER;

        // Get list of image and annotation files
        $images = collect(Storage::files($imageFolder));
        $annotations = collect(Storage::files($annotationFolder));

        // Initialize an empty arrays to store the parsed data

        $categories = [];
        $imageData = [];

        // Iterate over the annotations
        foreach ($annotations as $index => $annotationFile) {
            $annotationData = [];
            $annotationFileName = pathinfo($annotationFile, PATHINFO_FILENAME);

            // Find the corresponding image file
            $imageFile = $images->first(fn($image) => pathinfo($image, PATHINFO_FILENAME) === $annotationFileName);
            if (!$imageFile) {
                continue;
            }

            // Get the image's dimensions
            $absolutePath = storage_path($imageFile);
            list($imageWidth, $imageHeight) = getimagesize($absolutePath);

            $imageFileName = pathinfo($imageFile, PATHINFO_BASENAME);
            $imageData[] = [
                'img_folder' => self::IMAGE_FOLDER,
                'img_filename' => $imageFileName,
                'width' => $imageWidth,
                'height' => $imageHeight,
            ];

            // Read the annotation content
            $annotationContent = Storage::get($annotationFile);
            $lines = explode("\n", trim($annotationContent));

            // Parse each annotation line (each line represents an object in YOLO format)
            foreach ($lines as $line) {
                $data = explode(' ', $line);

                // Extract class ID and normalized bounding box or polygon points
                $classId = $data[0];
                if ($annotationTechnique === AppConstants::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                    $centerX = $data[1];
                    $centerY = $data[2];
                    $width = $data[3];
                    $height = $data[4];

                    $annotationData[] = [
                        'class_id' => $classId,
                        'center_x' => $centerX,
                        'center_y' => $centerY,
                        'width' => $width,
                        'height' => $height,
                        'segmentation' => null, // Not applicable for YOLO bbox
                    ];
                } elseif ($annotationTechnique === AppConstants::ANNOTATION_TECHNIQUES['POLYGON']) {
                    // Extract polygon points and initialize arrays
                    $polygonPoints = array_slice($data, 1);
                    $normalizedPoints = [];
                    $xPoints = [];
                    $yPoints = [];

                    foreach ($polygonPoints as $i => $point) {
                        if ($i % 2 === 0) {
                            // X coordinate
                            $xPoints[] = $point;
                        } else {
                            // Y coordinate
                            $yPoints[] = $point;
                        }
                        $normalizedPoints[] = $point;
                    }

                    $annotationData[] = [
                        'class_id' => $classId,
                        'segmentation' => json_encode($normalizedPoints),
                        'center_x' => (min($xPoints) + max($xPoints)) / 2,
                        'center_y' => (min($yPoints) + max($yPoints)) / 2,
                        'width' => max($xPoints) - min($xPoints),
                        'height' => max($yPoints) - min($yPoints),
                    ];
                }
            }
            $imageData[$index]['annotations'] = $annotationData;
        }
        $categories = $this->getCategories($folderName);

        if ($imageData && $annotationData && $categories) {
            return [
                'categories' => $categories,
                'images' => $imageData
            ];
        }
        return [];
    }

    private function getCategories($folderName): array
    {

        $dataFilePath = AppConstants::LIVEWIRE_TMP_PATH . $folderName . '/' . self::DATA_YAML;
        if (!Storage::exists($dataFilePath)) {
            return [];
        }
        // Read and parse the YAML file
        $dataContent = Storage::get($dataFilePath);
        $annotationData = Yaml::parse($dataContent);

        // Extract 'nc' and 'names' directly
        return [
            'nc' => $annotationData['nc'] ?? null,
            'names' => $annotationData['names'] ?? [],
        ];
    }


}
