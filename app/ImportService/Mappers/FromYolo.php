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

class FromYolo extends BaseFromMapper
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

        $imageData = $this->parseAnnotationFiles($images, $annotations, $annotationTechnique);
        $classes = $this->getClasses($folderName);

        if($imageData && $classes) {
            return Response::success(data:['images' => $imageData,'classes' => $classes,]);
        } else {
            return Response::error("Failed to map annotations");
        }
    }

    private function parseAnnotationFiles($images, $annotations, $annotationTechnique): array
    {
        $imageData = [];

        foreach ($annotations as $index => $annotationFile) {
            $annotationFileName = pathinfo($annotationFile, PATHINFO_FILENAME);

            // Find the corresponding image file
            foreach($images as $image) {
                $imageFileName = pathinfo($image, PATHINFO_FILENAME);
                if ($imageFileName === $annotationFileName) {
                    break;
                }
            }
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
            $points = array_slice($data, 1);

            $annotation = [
                'class_id' => $classId,
            ];

            if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX']) {
                $annotation += $this->transformBoundingBox($points);
            } elseif ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                $annotation += $this->createBboxFromPolygon($points);
                $annotation['segmentation'] = $this->transformPolygon($points);
            }
            $annotationData[] = $annotation;
        }

        return $annotationData;
    }

    public function transformBoundingBox(array $bbox, $imgDims = null): array
    {
        [$x, $y, $width, $height] = $bbox;

        return [
            'x' => $x - $width / 2,
            'y' => $y - $height / 2,
            'width' => $width,
            'height' => $height,
        ];
    }

    public function transformPolygon(array $polygonPoints, $imgDims = null): array
    {
        $normalizedPoints = [];
        foreach (array_chunk($polygonPoints, 2) as $pair) {
            $normalizedPoints[] = ['x' => $pair[0], 'y' => $pair[1]];
        }

        return $normalizedPoints;
    }

    private function createBboxFromPolygon(array $points)
    {
        // Get x values
        $xCoords = array_filter($points, fn($key) => $key % 2 == 0, ARRAY_FILTER_USE_KEY);
        // Get y values
        $yCoords = array_filter($points, fn($key) => $key % 2 != 0, ARRAY_FILTER_USE_KEY);

        $minX = min($xCoords);
        $minY = min($yCoords);
        $maxX = max($xCoords);
        $maxY = max($yCoords);

        return [
            'x' => $minX,
            'y' => $minY,
            'width' => $maxX - $minX,
            'height' => $maxY - $minY
        ];
    }

    public function getClasses($classesSource): array
    {

        $dataFilePath = AppConfig::LIVEWIRE_TMP_PATH . $classesSource . '/' . YoloConfig::DATA_YAML;
        if (!Storage::exists($dataFilePath)) {
            return [];
        }
        // Read and parse the YAML file
        $dataContent = Storage::get($dataFilePath);
        $annotationData = Yaml::parse($dataContent);

        return array_map(fn($name) => [
            'name' => $name,
        ], $annotationData['names']);
    }


}
