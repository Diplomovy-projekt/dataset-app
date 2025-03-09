<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Utils\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class ToYolo extends BaseMapper
{

    /**
     * @throws \Exception
     */
    public function handle(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        $this->linkImages($images, $datasetFolder);
        $this->createAnnotations($images, $datasetFolder, $annotationTechnique);
        $this->populateDataFile($datasetFolder);
    }

    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);

            foreach($image['annotations'] as $annotation) {
                $this->mapClass($annotation['class']['name']);

                $mappedAnnotation = $this->mapAnnotation($annotationTechnique, $annotation);

                File::ensureDirectoryExists($annotationPath);
                if (!Storage::append($annotationPath, $mappedAnnotation)) {
                    throw new \Exception("Failed to write annotation to file");
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function populateDataFile($datasetFolder): void
    {
        uasort($this->classMap, function ($a, $b) {
            return $a['id'] <=> $b['id'];
        });

        $yamlData = [
            'nc' => count($this->classMap),
            'names' => array_column($this->classMap, 'name'),
        ];

        File::ensureDirectoryExists(Storage::disk('datasets')->path($datasetFolder));
        if(!Storage::disk('datasets')->put($datasetFolder . '/data.yaml', Yaml::dump($yamlData, 2, 4))) {
            throw new \Exception("Failed to write data file");
        }
    }

    protected function mapBbox(mixed $annotation, array $imgDims = null): mixed
    {
        // Centering x and y coordinates because In DB they are stored as top-left corner
        $classId = $this->getClassId($annotation['class']['name']);
        $x = $annotation['x'] + $annotation['width'] / 2;
        $y = $annotation['y'] + $annotation['height'] / 2;
        $width = $annotation['width'];
        $height = $annotation['height'];

        return "$classId $x $y $width $height";
    }

    protected function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        $classId = $this->getClassId($annotation['class']['name']);
        $polygon = json_decode($annotation['segmentation'], true);

        $points = '';
        foreach ($polygon as $point) {
            $points .= $point['x'] . ' ' . $point['y'] . ' ';
        }

        return "$classId " . rtrim($points, ' ');
    }

    /**
     * @throws \Exception
     */
    protected function getImageDestinationPath($datasetFolder, $image): string
    {
        $path = AppConfig::DATASETS_PATH['public'] . $datasetFolder . '/' . YoloConfig::IMAGE_FOLDER . '/' . $image['filename'];
        return Storage::path($path);
    }


    /**
     * @throws \Exception
     */
    protected function getAnnotationDestinationPath($datasetFolder, $image = null): string
    {
        return AppConfig::DATASETS_PATH['public'] .
            $datasetFolder . '/' .
            YoloConfig::LABELS_FOLDER . '/' .
            pathinfo($image['filename'], PATHINFO_FILENAME) . '.' .
            YoloConfig::LABEL_EXTENSION;
    }
}
