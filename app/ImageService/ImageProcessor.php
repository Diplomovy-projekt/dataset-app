<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait ImageProcessor
{
    use ImageTransformer;

    /**
     * Creates thumbnails for each image
     * @param string $datasetFolderPath
     * @return Response
     */
    public function createThumbnails(string $datasetFolder, $images): array
    {
        if(!is_array($images)){
            $images = [$images];
        }
        if(empty($images)){
            return [];
        }

        $source = Storage::path(AppConfig::DEFAULT_DATASET_LOCATION . $datasetFolder."/".AppConfig::FULL_IMG_FOLDER);
        $destination = Storage::path(AppConfig::DEFAULT_DATASET_LOCATION . $datasetFolder."/".AppConfig::IMG_THUMB_FOLDER);
        FileUtil::ensureFolderExists($destination);
        $createdThumbnails = [];
        foreach($images as $image){
            if($this->rescale($source.$image, $destination.$image) === true){
                $createdThumbnails[] = $image;
            }
        }
        return $createdThumbnails;
    }

    public function createClassCrops(string $datasetFolder, Collection $images): array
    {
        $classCounts = [];
        foreach ($images as $image) {
            $imagePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConfig::FULL_IMG_FOLDER.$image->filename);

            foreach ($image->annotations as $index => $annotation) {
                $classId = $annotation->annotation_class_id;
                $directoryPath = $datasetFolder . '/' . AppConfig::CLASS_IMG_FOLDER . $classId;

                if(!isset($classCounts[$classId])) {
                    $classCounts[$classId] = count(Storage::disk('datasets')->files($directoryPath));
                }
                if ($classCounts[$classId] < AppConfig::SAMPLES_COUNT) {
                    $savePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConfig::CLASS_IMG_FOLDER.$classId.'/'.$annotation->id . "_" . $image->filename);
                    FileUtil::ensureFolderExists($savePath);

                    $pixelizedBbox = $this->pixelizeBbox(["x" => $annotation->x, "y" => $annotation->y, "width" => $annotation->width, "height" => $annotation->height], $image['width'], $image['height']);
                    $this->crop($pixelizedBbox, $imagePath,$savePath);
                    $this->drawAnnotations([$image->width, $image->height], $savePath, $annotation);
                    $this->rescale($savePath, $savePath);
                    $classCounts[$classId]++;
                }
            }
        }
        return $classCounts;
    }


    public function moveImages($imageFileNames, $from, $to): Response
    {
        foreach ($imageFileNames as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $source = rtrim($from, '/') . '/' . $file;
                $dest = rtrim($to, '/') . '/' . $file;

                try {
                    File::ensureDirectoryExists($dest);
                    if(!Storage::move($source, $dest)){
                        throw new \Exception("Failed to move image $file to $to");
                    }
                } catch (\Exception $e) {
                    Response::error(message:$e->getMessage());
                }
            }
        }

        return Response::success(data: ['datasetFolder' => $to]);
    }
}
