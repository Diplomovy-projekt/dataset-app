<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Utils\FileUtil;
use App\Utils\Response;
use Faker\Core\File;
use Illuminate\Support\Collection;
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

        $source = Storage::disk('datasets')->path($datasetFolder."/".AppConfig::FULL_IMG_FOLDER);
        $destination = Storage::disk('datasets')->path($datasetFolder."/".AppConfig::IMG_THUMB_FOLDER);
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

    /**
     * Moves images from a temporary folder to a public static dataset folder.
     *
     * @param string $sourceFolder Dataset folder in tmp storage (source of dataset).
     * @param string $imageFolder The subfolder within the temporary folder where the images are stored(it's specific to annotation format).
     * @return \App\Utils\Response A response indicating the success or failure of the operation.
     */
    public function moveImagesToPublicDataset($sourceFolder, $imageFolder, $destinationFolder = null): Response
    {
        $destinationFolder = $destinationFolder ?? $sourceFolder;

        $imageFolderPath = AppConfig::LIVEWIRE_TMP_PATH . $sourceFolder . '/' . $imageFolder;
        $files = Storage::files($imageFolderPath);

        if (empty($files)) {
            return Response::error("No images found in the dataset");
        }
        $filesMoved = [];
        // Iterate over each file in the folder
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            // Validate file type (only process images with supported extensions)
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $source = $file;
                $destination = AppConfig::DATASETS_PATH['public'] . $destinationFolder . '/' . AppConfig::FULL_IMG_FOLDER . $filename . '.' . $extension;

                try {
                    FileUtil::ensureFolderExists($destination);
                    Storage::disk('storage')->move($source, $destination);
                    $filesMoved[] = pathinfo($file, PATHINFO_BASENAME);
                } catch (\Exception $e) {
                    Response::error("An error occurred while moving images to public static storage: " . $e->getMessage());
                }
            }
        }

        return Response::success(data: ['images' => $filesMoved, 'destinationFolder' => $destinationFolder]);
    }
}
