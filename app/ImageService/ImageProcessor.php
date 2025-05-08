<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\Utils\FileUtil;
use App\Utils\Response;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait ImageProcessor
{
    use ImageTransformer;

    /**
     * Creates thumbnails for each image
     * @param string $datasetFolderPath
     * @throws DataException
     */
    public function createThumbnails(string $datasetFolder, $images): void
    {
        if(!is_array($images)){
            $images = [$images];
        }
        if(empty($images)){
            throw new DataException("No images found");
        }

        $datasetPath = Util::getDatasetPath($datasetFolder);
        $source = Storage::path($datasetPath . AppConfig::FULL_IMG_FOLDER);
        $destination = Storage::path($datasetPath . AppConfig::IMG_THUMB_FOLDER);
        FileUtil::ensureFolderExists($destination);
        $createdThumbnails = [];
        foreach($images as $image){
            if($this->rescale($source.$image, $destination.$image) === true){
                $createdThumbnails[] = $image;
            }
        }
        if(count($createdThumbnails) != count($images)){
            throw new DataException("Failed to create thumbnails for some images");
        }
    }

    public function createClassCrops(string $datasetFolder, Collection $images, array &$classCounts): void
    {
        $datasetPath = Util::getDatasetPath($datasetFolder);
        foreach ($images as $image) {
            $imagePath = Storage::path($datasetPath . AppConfig::FULL_IMG_FOLDER.$image->filename);

            foreach ($image->annotations as $index => $annotation) {
                $classId = $annotation->annotation_class_id;
                $directoryPath = $datasetPath . '/' . AppConfig::CLASS_IMG_FOLDER . $classId;

                if(!isset($classCounts[$classId])) {
                    $classCounts[$classId] = count(Storage::files($directoryPath));
                }
                if ($classCounts[$classId] < AppConfig::SAMPLES_COUNT) {
                    $savePath = Storage::path($datasetPath . AppConfig::CLASS_IMG_FOLDER.$classId.'/'.$annotation->id . "_" . $image->filename);
                    FileUtil::ensureFolderExists($savePath);

                    $pixelizedBbox = $this->pixelizeBbox(["x" => $annotation->x, "y" => $annotation->y, "width" => $annotation->width, "height" => $annotation->height], $image['width'], $image['height']);
                    $this->crop($pixelizedBbox, $imagePath,$savePath);
                    $this->drawAnnotations([$image->width, $image->height], $savePath, $annotation);
                    $this->rescale($savePath, $savePath);
                    $classCounts[$classId]++;
                }
            }
        }
    }

    /**
     * @throws DataException
     */
    public function moveImages($imageFileNames, $from, $to): void
    {
        foreach ($imageFileNames as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $source = rtrim($from, '/') . '/' . $file;
                $dest = rtrim($to, '/') . '/' . $file;

                File::ensureDirectoryExists($to);

                if (!Storage::move($source, $dest)) {
                    throw new DataException("Failed to move image $file to $to");
                }
            }
        }
    }
}
