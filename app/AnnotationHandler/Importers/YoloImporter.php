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

        $imageData = $this->parseAnnotationFiles($images, $annotations, $annotationTechnique);

        $categories = $this->getCategories($folderName);

        return $imageData && $categories
            ? ['categories' => $categories, 'images' => $imageData]
            : [];
    }

    private function parseAnnotationFiles($images, $annotations, $annotationTechnique): array
    {
        $imageData = [];

        foreach ($annotations as $index => $annotationFile) {
            $annotationFileName = pathinfo($annotationFile, PATHINFO_FILENAME);

            // Find the corresponding image file
            $imageFile = $images->first(fn($image) => pathinfo($image, PATHINFO_FILENAME) === $annotationFileName);
            if (!$imageFile) {
                continue;
            }

            // Get image dimensions
            $absolutePath = storage_path($imageFile);
            list($imageWidth, $imageHeight) = getimagesize($absolutePath);

            $imageFileName = pathinfo($imageFile, PATHINFO_BASENAME);
            $imageData[$index] = [
                'img_folder' => self::IMAGE_FOLDER,
                'img_filename' => $imageFileName,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'annotations' => $this->parseAnnotationsInFile($annotationFile, $annotationTechnique),
            ];
        }

        return $imageData;
    }

    private function parseAnnotationsInFile(string $annotationFile, string $annotationTechnique): array
    {
        $annotationData = [];
        $annotationContent = Storage::get($annotationFile);
        $lines = explode("\n", trim($annotationContent));

        foreach ($lines as $line) {
            $data = explode(' ', $line);
            $classId = $data[0];

            $segmentation = array_slice($data, 1);
            if ($annotationTechnique === AppConstants::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                $annotationData[] = $this->transformBoundingBox($classId, $segmentation);
            } elseif ($annotationTechnique === AppConstants::ANNOTATION_TECHNIQUES['POLYGON']) {
                $annotationData[] = $this->transformPolygon($classId, $segmentation);
            }
        }

        return $annotationData;
    }

    private function transformBoundingBox(string $classId, array $data): array
    {
        [$x, $y, $width, $height] = $data;

        return [
            'class_id' => $classId,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'segmentation' => null,
        ];
    }

    private function transformPolygon(string $classId, array $polygonPoints): array
    {
        $normalizedPoints = [];
        foreach (array_chunk($polygonPoints, 2) as $pair) {
            $normalizedPoints[] = ['x' => $pair[0], 'y' => $pair[1]];
        }

        $xPoints = array_column($normalizedPoints, 'x');
        $yPoints = array_column($normalizedPoints, 'y');

        return [
            'class_id' => $classId,
            'segmentation' => json_encode($normalizedPoints),
            'x' => min($xPoints),
            'y' => min($yPoints),
            'width' => max($xPoints) - min($xPoints),
            'height' => max($yPoints) - min($yPoints),
        ];
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
