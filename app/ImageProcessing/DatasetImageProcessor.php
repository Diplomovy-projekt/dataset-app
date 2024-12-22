<?php

namespace App\ImageProcessing;

use App\Models\Dataset;
use App\Traits\ImageHandler;
use App\Utils\AppConstants;
use App\Utils\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatasetImageProcessor
{
    use ImageHandler;

    /**
     * Processes images in the dataset
     * @param string $datasetFolder
     * @return Response
     */
    public function processImages(string $datasetFolder): Response
    {
        $datasetFolderPath = storage_path(AppConstants::DATASETS_PATH . $datasetFolder);

        $createdThumbnails = $this->createThumbnails($datasetFolder);
        if (!$createdThumbnails->isSuccessful()) {
            return Response::error("An error occurred while creating thumbnails");
        }
        $createdClassCrops = $this->createClassCrops($datasetFolder);
        if (!$createdClassCrops->isSuccessful()) {
            return Response::error("An error occurred while creating class crops");
        }
        return Response::success("Images processed successfully");
    }

    /**
     * Creates thumbnails for each image in the dataset
     * @param string $datasetFolderPath
     * @return Response
     */
    private function createThumbnails(string $datasetFolder): Response
    {
        $datasetsPath = Storage::disk('datasets')->path('');
        $outputFolderPath = $datasetsPath . $datasetFolder . '/' . AppConstants::IMG_THUMB_FOLDER;

        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConstants::IMG_THUMB_FOLDER);
        $files = Storage::disk('datasets')->files($datasetFolder.'/'.AppConstants::FULL_IMG_FOLDER);

        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            $thumbnailPath = $outputFolderPath . $fileName;
            $this->rescale($datasetsPath . $file, $thumbnailPath);
        }

        return Response::success("Thumbnails created successfully");
    }


    /**
     * Creates 3 crops for each class in the dataset
     * @param string $datasetFolderPath
     * @return Response
     */
    private function createClassCrops(string $datasetFolder): Response
    {
        Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConstants::CLASS_IMG_FOLDER);

        $dataset = Dataset::where('unique_name', $datasetFolder)->first();
        $totalImages = $dataset->num_images;
        $batchSize = max(ceil($dataset->num_images * 0.1), 1); // 10% of the dataset size

        $classes = $dataset->classes()->get()->toArray();

        foreach ($classes as $class) {
            $classCounts[$class['id']] = ['count' => 0, 'name' => $class['name']];
            Storage::disk('datasets')->makeDirectory($datasetFolder.'/'.AppConstants::CLASS_IMG_FOLDER.$class['id']);
        }

        // Process images in batches of 10% of the dataset size
        for($i = 0; $i < 10; $i++){
            $offset = $i * $batchSize;
            $images = $dataset->images()->with('annotations')->skip($offset)->take($batchSize)->get();

            foreach ($images as $image) {
                $imagePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConstants::FULL_IMG_FOLDER.$image->img_filename);
                $extension = pathinfo($image->img_filename, PATHINFO_EXTENSION);

                foreach ($image->annotations as $annotation) {
                    $classId = $annotation->annotation_class_id;

                    if ($classCounts[$classId]['count'] < 3) {
                        $savePath = Storage::disk('datasets')->path($datasetFolder.'/'.AppConstants::CLASS_IMG_FOLDER.$classId.'/'.AppConstants::CLASS_SAMPLE_PREFIX.$classCounts[$classId]['count'] . $image->img_filename.'.'.$extension);

                        $pixelizedBbox = $this->pixelizeBbox(["x" => $annotation->x, "y" => $annotation->y, "width" => $annotation->width, "height" => $annotation->height], $image['img_width'], $image['img_height']);
                        $this->crop($pixelizedBbox, $imagePath,$savePath);
                        $this->drawAnnotations([$image->img_width, $image->img_height], $savePath, $annotation, $dataset->annotation_technique);

                        $classCounts[$classId]['count']++;
                    }
                }
            }

            if (!in_array(false, array_map(fn($count) => $count['count'] >= 3, $classCounts))) {
                break;
            }

            if ($offset + $batchSize >= $totalImages) {
                break;
            }
        }

        return Response::success("Class crops created successfully");
    }

}
