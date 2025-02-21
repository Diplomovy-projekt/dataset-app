<?php

namespace App\DatasetActions;

use App\Configs\AppConfig;
use App\Exceptions\DatasetImportException;
use App\ExportService\ExportService;
use App\ImageService\ImageProcessor;
use App\Models\AnnotationClass;
use App\Models\Dataset;
use App\Utils\FileUtil;
use App\Utils\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DatasetActions
{
    use ImageProcessor;

    public function deleteDataset($unique_name): Response
    {
        Gate::authorize('delete-dataset', $unique_name);
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
        Gate::authorize('delete-dataset', $uniqueName);
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
                    if (str_contains($filename, '_' . $image->filename)) {
                        Storage::disk('datasets')->delete($file);
                    }
                }
                $image->delete();
            }

            FileUtil::deleteEmptyDirectories(AppConfig::DATASETS_PATH . $dataset->unique_name);
            $this->deleteUnusedClassesFromDb();
            $result = $this->createSamplesForClasses($dataset->unique_name, $dataset->classes->pluck('id')->toArray(), $dataset->images()->pluck('filename')->toArray());
            $dataset->updateImageCount(-count($ids));
            if (!$result->isSuccessful()) {
                throw new \Exception($result->message);
            }

            return Response::success();
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    public function createSamplesForClasses(string $datasetFolder, array $classesToSample, array $newImages): Response
    {
        $dataset = Dataset::where('unique_name', $datasetFolder)->firstOrFail();
        $batchSize = max(ceil(count($newImages) * 0.1), 10); // 10% of new images or at least 10
        $classCounts = [];

        try {
            for ($i = 0; $i < 10; $i++) {
                $batch = array_slice($newImages, $i * $batchSize, $batchSize);
                if (empty($batch)) break;

                // Ensure images belong to the correct dataset
                $images = $dataset->images()
                    ->whereIn('filename', $batch)
                    ->whereHas('annotations', fn($q) => $q->whereIn('annotation_class_id', $classesToSample))
                    ->with(['annotations' => fn($q) => $q->whereIn('annotation_class_id', $classesToSample)])
                    ->get();

                $classCounts = array_merge($classCounts, $this->createClassCrops($datasetFolder, $images));

                if ($this->allClassesSampled($classCounts, $classesToSample)) break;
            }
        } catch (DatasetImportException $e) {
            return Response::error($e->getMessage(), $e->getData());
        }

        return Response::success("Class crops created successfully");
    }

    private function allClassesSampled(array $classCounts, array $classesToSample): bool
    {
        foreach ($classesToSample as $classId) {
            if (($classCounts[$classId] ?? 0) < AppConfig::SAMPLES_COUNT) return false;
        }
        return true;
    }

    public function deleteUnusedClassesFromDb(): void
    {
        $classes = AnnotationClass::doesntHave('annotations')->get();
        foreach ($classes as $class) {
            $class->delete();
        }
    }
}
