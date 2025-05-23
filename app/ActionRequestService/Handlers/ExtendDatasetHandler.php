<?php

namespace App\ActionRequestService\Handlers;

use App\Configs\AppConfig;
use App\ImageService\ImageProcessor;
use App\Models\ActionRequest;
use App\Models\AnnotationClass;
use App\Models\AnnotationData;
use App\Models\Dataset;
use App\Utils\Response;
use App\Utils\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ExtendDatasetHandler extends BaseHandler
{
    use ImageProcessor;
    protected function validationRules(): array
    {
        return [
            'dataset_unique_name' => 'required|exists:datasets,unique_name',
            'child_unique_name' => 'required|exists:datasets,unique_name',
        ];
    }

    public function approve(array $payload): void
    {
        DB::beginTransaction();
        $childDataset = $payload['child_unique_name'];
        $parentDataset = $payload['dataset_unique_name'];
        $childPath = Util::getDatasetPath($childDataset);
        $parentPath = Util::getDatasetPath($parentDataset);
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
                    $annotation->annotation_class_id = $parentClasses[$childClass->name]->id;
                    $annotationArray = $annotation->toArray();
                    if (isset($annotationArray['segmentation']) && is_array($annotationArray['segmentation'])) {
                        $annotationArray['segmentation'] = json_encode($annotationArray['segmentation']);
                    }
                    $annotationsClassesToUpdate[] = $annotationArray;
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
                $childPath . AppConfig::FULL_IMG_FOLDER,
                $parentPath . AppConfig::FULL_IMG_FOLDER);
            // Move thumb images
            $this->moveImages(
                $childImages->pluck('filename')->toArray(),
                $childPath . AppConfig::IMG_THUMB_FOLDER,
                $parentPath . AppConfig::IMG_THUMB_FOLDER);
            // Move class directories
            if(isset($classesToAddToParent)) {
                foreach($classesToAddToParent as $classId) {
                    $childClassDir = $childPath . AppConfig::CLASS_IMG_FOLDER . $classId;
                    $parentClassDir = $parentPath . AppConfig::CLASS_IMG_FOLDER . $classId;
                    if(!Storage::move($childClassDir, $parentClassDir)){
                        throw new \Exception("Failed to move class directory");
                    }
                }
            }

            $this->datasetActions->assignColorsToClasses(datasetFolder: $parentDataset);
            $parent->updateImageCount(count($child->images));
            $parent->updateSize($childImages->pluck('size')->sum());
        } catch (\Exception $e) {
            Util::logException($e, 'approve Extend datasetHandler');
            DB::rollBack();

            foreach ($childImages as $image) {
                $fullImg = $parentPath . AppConfig::FULL_IMG_FOLDER . $image['filename'];
                $thumbnail = $parentPath . AppConfig::IMG_THUMB_FOLDER . $image['filename'];

                if (Storage::exists($fullImg)) {
                    Storage::delete($fullImg);
                }
                if (Storage::exists($thumbnail)) {
                    Storage::delete($thumbnail);
                }
            }

            throw new \Exception('Failed to extend dataset');
        }

        $child->delete();
        if (!Storage::deleteDirectory($childPath)) {
            throw new \Exception("Failed to delete child dataset folder");
        }
        DB::commit();
    }

    public function reject(array $payload): void
    {
        $this->datasetActions->deleteDataset($payload['child_unique_name']);
    }

    public function getReviewUrl(Model $request): string
    {
        $payload = $request->payload;
        $uniqueName = $payload['child_unique_name'];

        return route('dataset.review.extend', [
            'uniqueName' => $uniqueName,
            'requestId' => $request->id,
        ]);
    }

    public function adminResponse(Model $request): mixed
    {
        $currentRoute = URL::livewireCurrent(true);
        $adminDatasetRoute = route('admin.datasets');
        if($currentRoute === $adminDatasetRoute) {
            return ['type' => 'success', 'message' => 'Dataset extended successfully'];
        }
        return ['route' => 'dataset.show', 'params' => ['uniqueName' => $request->dataset->unique_name]];
    }
    public function errorResponse(string $errorMessage, ActionRequest $request = null): mixed
    {
        if($request){
            $request->delete();
        }
        return ['type' => 'error', 'message' => $errorMessage];
    }
}
