<?php

namespace App\DatasetActions;

use App\Configs\AppConfig;
use App\Exceptions\DatasetImportException;
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

    public function deleteDataset($unique_name): Response
    {
        Gate::authorize('delete-dataset', $unique_name);
        try {
            $dataset = Dataset::where('unique_name', $unique_name)->first();
            $datasetPath = Util::getDatasetPath($dataset);
            $dataset->delete();
            if (Storage::exists($datasetPath)) {
                Storage::delete($datasetPath);
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
            $datasetPath = Util::getDatasetPath($dataset);
            $images = $dataset->images()->whereIn('id', $ids)->get();
            $images = Image::whereIn('id', $ids)->get();
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

    /**
     * @throws \Exception
     */
    public function mergeChildToParent($parentDataset, $childDataset): Response
    {
        Gate::authorize('delete-dataset', $parentDataset);
        Gate::authorize('delete-dataset', $childDataset);
        DB::beginTransaction();
        $childPath = AppConfig::DEFAULT_DATASET_LOCATION . $childDataset;
        $parentPath = AppConfig::DEFAULT_DATASET_LOCATION . $parentDataset;
        try {
            $parent = Dataset::where('unique_name', $parentDataset)->first();
            $parentClasses = $parent->classes()->get()->keyBy('name');

            $child = Dataset::where('unique_name', $childDataset)->first();
            $childImages = $child->images()->get();
            $childAnnotations = AnnotationData::whereIn('image_id', $childImages->pluck('id'))->get();
            $childClasses = $child->classes()->get()->keyBy('id');

            // 1. Assign child images to parent dataset
            $child->images()
                ->whereIn('id', $child->images()->pluck('id')->toArray())
                ->update(['dataset_folder' => $parent->unique_name, 'dataset_id' => $parent->id]);


            // 2. Reindex child annotation_class_id to parent class_id if they match in name
            $annotationsClassesToUpdate = [];
            $classesToAddToParent = [];
            foreach ($childAnnotations as $annotation) {
                $childClass = $childClasses[$annotation->annotation_class_id] ?? null;
                // If class exists in parent, update annotation to parent class
                if ($childClass && isset($parentClasses[$childClass->name])) {
                    $annotation->created_at = null;
                    $annotation->updated_at = null;
                    $annotation->annotation_class_id = $parentClasses[$childClass->name]->id;
                    $annotationsClassesToUpdate[] = $annotation->toArray();
                    continue;
                }
                // If new class, add to parent dataset
                if (!in_array($childClass->id, $classesToAddToParent, true)) {
                    $classesToAddToParent[] = $childClass->id;
                }
            }
            AnnotationClass::whereIn('id', $classesToAddToParent ?? [])
                ->update(['dataset_id' => $parent->id]);
            AnnotationData::upsert($annotationsClassesToUpdate, ['id'], ['annotation_class_id']);

            // Move files from child to parent
            // Move full images
            $this->moveImages(
                $childImages->pluck('filename')->toArray(),
                $childPath . '/' . AppConfig::FULL_IMG_FOLDER,
                $parentPath . '/' . AppConfig::FULL_IMG_FOLDER);
            // Move thumb images
            $this->moveImages(
                $childImages->pluck('filename')->toArray(),
                $childPath . '/' . AppConfig::IMG_THUMB_FOLDER,
                $parentPath . '/' . AppConfig::IMG_THUMB_FOLDER);
            // Move class directories
            if(isset($classesToAddToParent)) {
                foreach($classesToAddToParent as $classId) {
                    $childClassDir = $childPath . '/' . AppConfig::CLASS_IMG_FOLDER . $classId;
                    $parentClassDir = $parentPath . '/' . AppConfig::CLASS_IMG_FOLDER . $classId;
                    if(!Storage::move($childClassDir, $parentClassDir)){
                        throw new \Exception("Failed to move class directory");
                    }
                }
            }

            $parent->updateImageCount(count($child->images));
            $parent->updateSize($childImages->pluck('size')->sum());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($childImages as $image) {
                $fullImg = $parentPath . '/' . AppConfig::FULL_IMG_FOLDER . $image['filename'];
                $thumbnail = $parentPath . '/' . AppConfig::IMG_THUMB_FOLDER . $image['filename'];
                if(Storage::disk('storage')->exists($fullImg)){
                    Storage::disk('storage')->delete($fullImg);
                }
                if(Storage::disk('storage')->exists($thumbnail)){
                    Storage::disk('storage')->delete($thumbnail);
                }
            }
            return Response::error($e->getMessage());
        } finally {
            $child->delete();
            $dir = AppConfig::DEFAULT_DATASET_LOCATION . $childDataset;
            if(!Storage::deleteDirectory($dir)) {
                throw new \Exception("Failed to delete child dataset folder");
            }
            DB::commit();
            return Response::success('Datasets merged successfully');
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

    public function addUniqueSuffixes($datasetFolder, &$mappedData): Response
    {
        $images = &$mappedData['images'];
        $datasetPath = AppConfig::DEFAULT_DATASET_LOCATION . $datasetFolder . '/' . AppConfig::FULL_IMG_FOLDER;

        try {
            foreach ($images as &$image) {
                $suffix = uniqid('_da_');
                $newName = pathinfo($image['filename'], PATHINFO_FILENAME) . $suffix . '.' . pathinfo($image['filename'], PATHINFO_EXTENSION);

                $oldPath = $datasetPath . '/' . $image['filename'];
                $newPath = $datasetPath . '/' . $newName;

                if (Storage::move($oldPath, $newPath)) {
                    $image['filename'] = $newName;
                }
            }

            return Response::success();
        } catch (\Exception $e) {
            return Response::error("Failed to add unique suffixes to images", $e->getMessage());
        }
    }
}
