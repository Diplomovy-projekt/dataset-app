<?php

namespace App\ImportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Symfony\Component\Yaml\Yaml;

class YoloMapper
{
    public function parse(string $folderName, $annotationTechnique): Response
    {
        // Define folder paths
        $datasetPath = AppConfig::LIVEWIRE_TMP_PATH . $folderName;
        $imageFolder = $datasetPath . '/' . YoloConfig::IMAGE_FOLDER;
        $annotationFolder = $datasetPath . '/' . YoloConfig::LABELS_FOLDER;

        // Get list of image and annotation files
        $images = collect(Storage::files($imageFolder));
        $annotations = collect(Storage::files($annotationFolder));

        // Add unique identifier to each image
        $this->addUniqueSuffixes($images, $annotations);

        $imageData = $this->parseAnnotationFiles($images, $annotations, $annotationTechnique);

        $classes = $this->getClasses($folderName);

        return $imageData && $classes
            ? Response::success(data:[
                'images' => $imageData,
                'classes' => $classes,
            ])
            : Response::error("Failed to map annotations");
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
            $absolutePath = Storage::path($imageFile);
            list($imageWidth, $imageHeight) = getimagesize($absolutePath);


            $imageFileName = pathinfo($imageFile, PATHINFO_BASENAME);
            $imageData[$index] = [
                'filename' => $imageFileName,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'size' => filesize($absolutePath),
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
            if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                $annotationData[] = $this->transformBoundingBox($classId, $segmentation);
            } elseif ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
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
            'x' => $x - $width / 2, // Convert center to top-left
            'y' => $y - $height / 2, // Convert center to top-left
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

    private function getClasses($folderName): array
    {

        $dataFilePath = AppConfig::LIVEWIRE_TMP_PATH . $folderName . '/' . YoloConfig::DATA_YAML;
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

    private function addUniqueSuffixes(Collection &$images, Collection &$annotations)
    {
        $images = $images->sort()->values();
        $annotations = $annotations->sort()->values();

        if ($images->count() !== $annotations->count()) {
            throw new \Exception("Mismatched count: Images and annotations do not align.");
        }

        foreach ($images as $index => $image) {
            $suffix = uniqid('_da_');

            // Update the image
            $newImagePath = FileUtil::addUniqueSuffix($image, $suffix);
            Storage::move($image, FileUtil::addUniqueSuffix($image, $suffix));
            $images[$index] = $newImagePath;

            // Update the corresponding annotation
            $annotation = $annotations[$index];
            $newAnnotationPath = FileUtil::addUniqueSuffix($annotation, $suffix);
            Storage::move($annotation, $newAnnotationPath);
            $annotations[$index] = $newAnnotationPath;
        }
    }
}
