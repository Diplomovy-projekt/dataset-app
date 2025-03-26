<?php

namespace App\Utils;

use App\Models\Image;
use Illuminate\Support\Facades\DB;

class ImageQuery
{
    private $query;
    private ?int $perPage = null;
    private ?array $classIds = null;

    public function __construct(array $selectedDatasets)
    {
        $datasetIds = array_values(array_filter($selectedDatasets, 'is_numeric', ARRAY_FILTER_USE_KEY))
            ?: array_values($selectedDatasets);

        $this->query = Image::whereIn('dataset_id', $datasetIds)
            ->select(['id', 'filename', 'dataset_folder', 'dataset_id', 'width', 'height']);
    }

    public static function forDatasets(array|int $selectedDatasets): self
    {
        return new self($selectedDatasets);
    }

    public function excludeImages(?array $imageIds): self
    {
        if (!empty($imageIds)) {
            $this->query->whereNotIn('id', $imageIds);
        }
        return $this;
    }

    public function includeImages(?array $imageIds): self
    {
        if (!empty($imageIds)) {
            $this->query->whereIn('id', $imageIds);
        }
        return $this;
    }

    public function filterByClassIds(?array $classIds): self
    {
        if (!empty($classIds)) {
            $this->query->whereHas('annotations.class', function ($q) use ($classIds) {
                $q->whereIn('id', $classIds);
            });
            $this->classIds = $classIds;
        }
        return $this;
    }

    public function search(?string $term): self
    {
        $trimmed = trim($term ?? '');
        if ($trimmed !== '') {
            $this->query->where('filename', 'like', "%{$trimmed}%");
        }
        return $this;
    }

    public function perPage(?int $count): self
    {
        if (!empty($count)) {
            $this->perPage = $count;
        }
        return $this;
    }

    public function get()
    {
        $images = $this->perPage ? $this->query->paginate($this->perPage) : $this->query->get();
        $this->attachAnnotationsAndClasses($images);
        return $images;
    }

    private function attachAnnotationsAndClasses($images)
    {
        // Ensure we always have a collection (works for both paginator & collection)
        $collection = method_exists($images, 'getCollection') ? $images->getCollection() : $images;

        $imageIds = $collection->pluck('id')->toArray();
        if (empty($imageIds)) {
            return;
        }

        // Fetch annotations
        $annotations = $this->fetchAnnotations($imageIds);

        // Fetch only used annotation classes
        $usedClassIds = collect($annotations)->flatten(1)->pluck('annotation_class_id')->unique()->toArray();
        $annotationClasses = $this->fetchAnnotationClasses($usedClassIds);

        // Attach data
        $collection->each(function ($image) use ($annotations, $annotationClasses) {
            $imageAnnotations = $annotations[$image->id] ?? [];

            foreach ($imageAnnotations as &$annotation) {
                $annotation['class'] = $annotationClasses[$annotation['annotation_class_id']] ?? null;
            }

            $image->setRelation('annotations', collect($imageAnnotations));
        });
    }

    private function fetchAnnotations(array $imageIds)
    {
        $query = DB::table('annotation_data')
            ->select('id', 'image_id', 'x', 'y', 'width', 'height', 'annotation_class_id', 'segmentation')
            ->whereIn('image_id', $imageIds);

        if (!empty($this->classIds)) {
            $query->whereIn('annotation_class_id', $this->classIds);
        }

        return $query->get()
            ->map(function ($annotation) {
                $annotation->segmentation = json_decode($annotation->segmentation, true);
                return (array) $annotation;
            })
            ->groupBy('image_id')
            ->toArray();
    }

    private function fetchAnnotationClasses(array $classIds)
    {
        if (empty($classIds)) {
            return [];
        }

        return DB::table('annotation_classes')
            ->whereIn('id', $classIds)
            ->select('id', 'name', 'rgb')
            ->get()
            ->mapWithKeys(fn($class) => [$class->id => (array) $class])
            ->toArray();
    }
}
