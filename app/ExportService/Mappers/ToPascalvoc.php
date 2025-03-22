<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\LabelmeConfig;
use App\Configs\Annotations\PascalvocConfig;
use App\Configs\AppConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ToPascalvoc extends BaseToMapper
{
    protected static string $configClass = PascalvocConfig::class;

    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);

            // Create XML DOM document
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = true;

            // Root element
            $annotation = $doc->createElement('annotation');
            $doc->appendChild($annotation);

            // Add basic image information
            $this->addElement($doc, $annotation, 'folder', null);
            $this->addElement($doc, $annotation, 'filename', $image['filename']);
            $this->addElement($doc, $annotation, 'path',  $image['filename']);

            // Add size information
            $size = $doc->createElement('size');
            $annotation->appendChild($size);
            $this->addElement($doc, $size, 'width', $image['width']);
            $this->addElement($doc, $size, 'height', $image['height']);
            $this->addElement($doc, $size, 'depth', 3); // Assuming RGB images

            // Add segmented flag
            $this->addElement($doc, $annotation, 'segmented', $annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON'] ? 1 : 0);

            // Add each object (annotation)
            foreach ($image['annotations'] as $annotation_data) {
                $this->mapClass($annotation_data['class']['name']);
                $imgDims = [$image['width'], $image['height']];

                $object = $doc->createElement('object');
                $annotation->appendChild($object);

                $this->addElement($doc, $object, 'name', $annotation_data['class']['name']);
                $this->addElement($doc, $object, 'pose', 'Unspecified');
                $this->addElement($doc, $object, 'truncated', 0);
                $this->addElement($doc, $object, 'difficult', 0);

                $bndbox = $doc->createElement('bndbox');
                $object->appendChild($bndbox);

                $absoluteBbox = $this->mapAnnotation(AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'], $annotation_data, $imgDims);

                $this->addElement($doc, $bndbox, 'xmin', round($absoluteBbox['xmin']));
                $this->addElement($doc, $bndbox, 'ymin', round($absoluteBbox['ymin']));
                $this->addElement($doc, $bndbox, 'xmax', round($absoluteBbox['xmax']));
                $this->addElement($doc, $bndbox, 'ymax', round($absoluteBbox['ymax']));

                if ($annotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
                    $polygon = $doc->createElement('polygon');
                    $object->appendChild($polygon);

                    $absolutePolygon = $this->mapAnnotation(AppConfig::ANNOTATION_TECHNIQUES['POLYGON'], $annotation_data, $imgDims);
                    $this->addPointsToPolygon($doc, $polygon, $absolutePolygon);
                }
            }

            File::ensureDirectoryExists($annotationPath);
            if (!Storage::put($annotationPath, $doc->saveXML())) {
                throw new \Exception("Failed to write annotation to file");
            }
        }
    }

    public function getAnnotationDestinationPath(string $datasetFolder, array $image = null): string
    {
        return AppConfig::DATASETS_PATH['public'] .
            $datasetFolder . '/' .
            pathinfo($image['filename'], PATHINFO_FILENAME) . '.' .
            PascalvocConfig::LABEL_EXTENSION;
    }

    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        $polygon = $annotation['segmentation'];
        $points = [];

        foreach ($polygon as $index => $point) {
            $points["x{$index}"] = round($point['x'] * $imgDims[0]);
            $points["y{$index}"] = round($point['y'] * $imgDims[1]);
        }

        return $points;
    }

    public function mapBbox(mixed $annotation, array $imgDims = null): mixed
    {
        // Convert normalized coordinates to absolute pixel values
        $x = $annotation['x'] * $imgDims[0];
        $y = $annotation['y'] * $imgDims[1];
        $width = $annotation['width'] * $imgDims[0];
        $height = $annotation['height'] * $imgDims[1];

        return [
            'xmin' => $x,
            'ymin' => $y,
            'xmax' => $x + $width,
            'ymax' => $y + $height
        ];
    }

    // Helper method to add elements to the XML document
    private function addElement(\DOMDocument $doc, \DOMElement $parent, string $name, $value): void
    {
        $element = $doc->createElement($name, $value);
        $parent->appendChild($element);
    }

    // Helper method to add polygon points to the XML document
    private function addPointsToPolygon(\DOMDocument $doc, \DOMElement $polygon, array $points): void
    {
        foreach ($points as $key => $value) {
            $this->addElement($doc, $polygon, $key, $value);
        }
    }

}
