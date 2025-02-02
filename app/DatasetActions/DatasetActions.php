<?php

namespace App\DatasetActions;

use App\Configs\AppConfig;
use App\Exceptions\DatasetImportException;
use App\ImageService\ImageProcessor;
use App\Models\AnnotationClass;
use App\Models\Dataset;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Facades\Storage;

class DatasetActions
{
    use ImageProcessor;

    public function deleteDataset($unique_name): Response
    {
        try {
            $dataset = Dataset::where('unique_name', $unique_name)->first();
            $dataset->delete();
            if (Storage::disk('datasets')->exists($dataset->unique_name)) {
                Storage::disk('datasets')->deleteDirectory($dataset->unique_name);
            }
            return Response::success('Dataset deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function deleteImages($uniqueName, $ids): Response
    {
        try {
            $dataset = Dataset::where('unique_name', $uniqueName)->first();
            $images = $dataset->images()->whereIn('id', $ids)->get();
            foreach ($images as $image) {
                Storage::disk('datasets')->delete($dataset->unique_name . '/' . AppConfig::FULL_IMG_FOLDER . $image->filename);
                Storage::disk('datasets')->delete($dataset->unique_name . '/' . AppConfig::IMG_THUMB_FOLDER . $image->filename);
                // Delete all class images
                $files = Storage::disk('datasets')->allFiles($dataset->unique_name . '/' . AppConfig::CLASS_IMG_FOLDER);
                foreach ($files as $file) {
                    $filename = pathinfo($file, PATHINFO_BASENAME);
                    if (strpos($filename, '_' . $image->filename) !== false) {
                        Storage::disk('datasets')->delete($file);
                    }
                }
                $image->delete();
            }

            FileUtil::deleteEmptyDirectories(AppConfig::DATASETS_PATH . $dataset->unique_name);
            $this->deleteUnusedClasses();
            $result = $this->createSamplesForClasses($dataset->unique_name, $dataset->classes->pluck('id')->toArray());
            $dataset->updateImageCount(-count($ids));
            if (!$result->isSuccessful()) {
                throw new \Exception($result->message);
            }

            return Response::success();
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function createSamplesForClasses(string $datasetFolder, array $classesToSample): Response
    {
        $dataset = Dataset::where('unique_name', $datasetFolder)->first();
        $batchSize = max(ceil($dataset->num_images * 0.1), 10); // 10% of the dataset size

        $classCounts = [];
        // We are creating crops for classes in batches of 10% images because most likely
        // we will get AppConfig::SAMPLES_COUNT crops per class sooner than parsing through whole dataset
        try {
            for ($i = 0; $i < 10; $i++) {
                $offset = $i * $batchSize;
                // Fetch images in the batch with annotations belonging to classes to sample
                $images = $dataset->images()
                    ->whereHas('annotations', function ($query) use ($classesToSample) {
                        $query->whereIn('annotation_class_id', $classesToSample);
                    })
                    ->with(['annotations' => function ($query) use ($classesToSample) {
                        $query->whereIn('annotation_class_id', $classesToSample);
                    }])
                    ->skip($offset)
                    ->take($batchSize)
                    ->get();

                $classCounts = array_merge($classCounts, $this->createClassCrops($datasetFolder, $images));

                $allClassesSampled = true;
                foreach ($classesToSample as $classId) {
                    // If a class does not exist in classCounts or doesn't have enough samples, keep sampling
                    if (!isset($classCounts[$classId]) || $classCounts[$classId] < AppConfig::SAMPLES_COUNT) {
                        $allClassesSampled = false;
                        break;
                    }
                }
                if ($offset + $batchSize >= $dataset->num_images || $allClassesSampled) {
                    break;
                }
            }
        } catch (DatasetImportException $e) {
            return Response::error($e->getMessage(), $e->getData());
        }
        return Response::success("Class crops created successfully");
    }

    public function deleteUnusedClasses(): void
    {
        $classes = AnnotationClass::doesntHave('annotations')->get();
        foreach ($classes as $class) {
            $class->delete();
        }
    }

    public function buildDataset($images): Response
    {
        //build new custom dataset from images. images contain annotations and classes.


        return Response::success();

    }
}
