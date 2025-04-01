<?php

namespace App\DatasetActions;

use App\Configs\AppConfig;
use App\Exceptions\DataException;
use App\ExportService\ExportService;
use App\ImageService\ImageProcessor;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\FileUtil;
use App\Utils\Response;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DatasetActions
{
    use ImageProcessor;

    public function deleteDataset($unique_name): void
    {
        Gate::authorize('delete-dataset', $unique_name);

        $dataset = Dataset::where('unique_name', $unique_name)->first();
        if (!$dataset) {
            throw new \Exception('Dataset not found.');
        }

        $datasetPath = rtrim(Util::getDatasetPath($dataset), '/\\');

        DB::beginTransaction();
        $dataset->delete();

        if (Storage::exists($datasetPath)) {
            if (!Storage::deleteDirectory($datasetPath)) {
                DB::rollBack();
                throw new \Exception('Failed to delete the dataset file.');
            }
        }
        DB::commit();
    }

    public function deleteImages($uniqueName, $ids): Response
    {
        Gate::authorize('delete-dataset', $uniqueName);
        try {
            $dataset = Dataset::where('unique_name', $uniqueName)->first();
            $datasetPath = Util::getDatasetPath($dataset);
            $images = $dataset->images()->whereIn('id', $ids)->get();
            foreach ($images as $image) {
                Storage::delete($datasetPath . AppConfig::FULL_IMG_FOLDER . $image->filename);
                Storage::delete($datasetPath . AppConfig::IMG_THUMB_FOLDER . $image->filename);
                // Delete all class images
                $files = Storage::allFiles($datasetPath . AppConfig::CLASS_IMG_FOLDER);
                foreach ($files as $file) {
                    $filename = pathinfo($file, PATHINFO_BASENAME);
                    if (str_contains($filename, '_' . $image->filename)) {
                        Storage::delete($datasetPath . '/' . $file);
                    }
                }
                $image->delete();
            }

            FileUtil::deleteEmptyDirectories($datasetPath);
            $this->deleteUnusedClassesFromDb();
            $result = $this->createSamplesForClasses($dataset->unique_name, $dataset->classes->pluck('id')->toArray(), $dataset->images()->pluck('filename')->toArray());
            $dataset->updateImageCount();
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
        } catch (DataException $e) {
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

    private function deleteUnusedClassesFromDb(): void
    {
        $classes = AnnotationClass::doesntHave('annotations')->get();
        foreach ($classes as $class) {
            $class->delete();
        }
    }

    public function assignColorsToClasses(array $classIds = null, string $datasetFolder = null): void
    {
        if ($datasetFolder) {
            $classIds = Dataset::where('unique_name', $datasetFolder)->first()->classes->pluck('id')->toArray();
        }

        $coloredClasses = Util::generateDistinctColors($classIds);

        $updateData = [];
        foreach ($coloredClasses as $id => $color) {
            $updateData[] = "WHEN `id` = '$id' THEN '$color'";
        }

        $ids = implode(", ", array_keys($coloredClasses));
        $updates = implode(" ", $updateData);

        DB::statement("
            UPDATE annotation_classes
            SET rgb = CASE $updates END
            WHERE id IN ($ids)
        ");
    }

    /**
     * @throws DataException
     */
    public function addUniqueSuffixes($datasetFolder, &$mappedData): void
    {
        $images = &$mappedData['images'];
        $datasetPath = AppConfig::DATASETS_PATH['private'] . $datasetFolder . '/' . AppConfig::FULL_IMG_FOLDER;

        foreach ($images as &$image) {
            $suffix = uniqid('_da_');
            $newName = pathinfo($image['filename'], PATHINFO_FILENAME) . $suffix . '.' . pathinfo($image['filename'], PATHINFO_EXTENSION);

            $oldPath = $datasetPath . '/' . $image['filename'];
            $newPath = $datasetPath . '/' . $newName;

            if (!Storage::move($oldPath, $newPath)) {
                throw new DataException("Failed to move file: {$oldPath} to {$newPath}");
            }

            $image['filename'] = $newName;
        }
    }


    public static function moveDatasetTo(string $uniqueName, string $targetVisibility): Response
    {
        if (!in_array($targetVisibility, ['public', 'private'])) {
            return Response::error("Invalid target visibility");
        }

        $fromPath = AppConfig::DATASETS_PATH[$targetVisibility === 'public' ? 'private' : 'public'] . $uniqueName;
        $toPath = AppConfig::DATASETS_PATH[$targetVisibility] . $uniqueName;

        if (!Storage::move($fromPath, $toPath)) {
            return Response::error("Failed to move dataset");
        }

        Dataset::where('unique_name', $uniqueName)->update(['is_public' => $targetVisibility === 'public']);

        return Response::success("Dataset moved to $targetVisibility");
    }

}
