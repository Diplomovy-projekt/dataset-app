<?php

namespace App\ImageService;

use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Utils\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DatasetImageProcessor
{
    use ImageTransformer;

    private $classCounts = [];

    public function createThumbnailsForNewDataset(string $datasetFolder)
    {
        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConfig::IMG_THUMB_FOLDER);
        $files = Storage::disk('datasets')->files($datasetFolder.'/'.AppConfig::FULL_IMG_FOLDER);

        $thumbnails = $this->createThumbnails($datasetFolder, $files);
        if (count($thumbnails) == count($files)) {
            return Response::success("Thumbnails created successfully", data: $thumbnails[0]);
        }

        return Response::error("Failed to create thumbnails for some images");
    }
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
        $datasetsPath = Storage::disk('datasets')->path('');
        $outputFolderPath = $datasetsPath . $datasetFolder . '/' . AppConfig::IMG_THUMB_FOLDER;

        $thumbnails = [];
        foreach ($images as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            $thumbnailPath = $outputFolderPath . $fileName;
            if($this->rescale($datasetsPath . $file, $thumbnailPath)){
                $thumbnails[] = $fileName;
            }
        }
        return $thumbnails;
    }


    /**
     * Creates 3 crops for each class in the dataset
     * @param string $datasetFolderPath
     * @return Response
     */
    public function createClassCropsForNewDataset(string $datasetFolder): Response
    {
        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConfig::CLASS_IMG_FOLDER);

        $dataset = Dataset::where('unique_name', $datasetFolder)->first();
        $batchSize = max(ceil($dataset->num_images * 0.1), 1); // 10% of the dataset size


        // We are creating crops for classes in batches of 10% images because most likely
        // we will get 3 crops per class sooner than parsing through whole dataset
        for($i = 0; $i < 10; $i++){
            $offset = $i * $batchSize;
            $images = $dataset->images()->with('annotations')->skip($offset)->take($batchSize)->get();

            $this->createClassCrops($datasetFolder, $images);
            if (!in_array(false, array_map(fn($count) => $count['count'] >= 3, $this->classCounts))) {
                break;
            }

            if ($offset + $batchSize >= $dataset->num_images) {
                break;
            }
        }
        return Response::success("Class crops created successfully");
    }

    public function createClassCrops(string $datasetFolder, Collection $images): void
    {
        foreach ($images as $image) {
            $imagePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConfig::FULL_IMG_FOLDER.$image->filename);

            foreach ($image->annotations as $annotation) {
                $classId = $annotation->annotation_class_id;
                $directoryPath = $datasetFolder . '/' . AppConfig::CLASS_IMG_FOLDER . $classId;

                if (!Storage::disk('datasets')->exists($directoryPath)) {
                    Storage::disk('datasets')->makeDirectory($directoryPath);
                }

                if (!isset($this->classCounts[$classId]['count'])) {
                    $this->classCounts[$classId]['count'] = 0;
                }

                if ($this->classCounts[$classId]['count'] < 3) {
                    $savePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConfig::CLASS_IMG_FOLDER.$classId.'/'.AppConfig::CLASS_SAMPLE_PREFIX.$this->classCounts[$classId]['count'] . $image->filename);
                    $pixelizedBbox = $this->pixelizeBbox(["x" => $annotation->x, "y" => $annotation->y, "width" => $annotation->width, "height" => $annotation->height], $image['width'], $image['height']);
                    $this->crop($pixelizedBbox, $imagePath,$savePath);
                    $this->drawAnnotations([$image->width, $image->height], $savePath, $annotation);
                    $this->rescale($savePath, $savePath);
                    $this->classCounts[$classId]['count']++;
                }
            }
        }
    }

}
