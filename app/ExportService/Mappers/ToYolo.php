<?php

namespace App\ExportService\Mappers;

use App\Configs\Annotations\YoloConfig;
use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\Utils\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class ToYolo extends BaseToMapper
{
    protected static string $configClass = YoloConfig::class;

    /**
     * @throws \Exception
     */
    public function customHandle(string $datasetFolder): void
    {
        $this->populateDataFile($datasetFolder);
    }

    public function createAnnotations(array $images, string $datasetFolder, string $annotationTechnique): void
    {
        foreach ($images as $image) {
            $annotationPath = $this->getAnnotationDestinationPath($datasetFolder, $image);
            $annotations = [];

            foreach ($image['annotations'] as $annotation) {
                $this->mapClass($annotation['class']['name']);
                $annotations[] = $this->mapAnnotation($annotationTechnique, $annotation);
            }

            File::ensureDirectoryExists($annotationPath);
            if (!Storage::put($annotationPath, implode("\n", $annotations))) {
                throw new \Exception("Failed to write annotations to file");
            }
        }
    }

    public function mapBbox(mixed $annotation, array $imgDims = null): mixed
    {
        // Centering x and y coordinates because In DB they are stored as top-left corner
        $classId = $this->getClassId($annotation['class']['name']);
        $x = $annotation['x'] + $annotation['width'] / 2;
        $y = $annotation['y'] + $annotation['height'] / 2;
        $width = $annotation['width'];
        $height = $annotation['height'];

        return "$classId $x $y $width $height";
    }

    public function mapPolygon(mixed $annotation, array $imgDims = null): mixed
    {
        $classId = $this->getClassId($annotation['class']['name']);
        $polygon = $annotation['segmentation'];

        $points = '';
        foreach ($polygon as $point) {
            $points .= $point['x'] . ' ' . $point['y'] . ' ';
        }

        return "$classId " . rtrim($points, ' ');
    }


    /**
     * @throws DataException
     */
    public function getAnnotationDestinationPath($datasetFolder, $image = null): string
    {
        return AppConfig::DATASETS_PATH['public'] .
            $datasetFolder . '/' .
            YoloConfig::LABELS_FOLDER . '/' .
            pathinfo($image['filename'], PATHINFO_FILENAME) . '.' .
            YoloConfig::LABEL_EXTENSION;
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
}
