<?php

namespace App\Utils;

use App\Configs\AppConfig;
use App\Models\Image;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImageQuery
{
    private $query;
    private ?int $perPage = null;
    private ?array $classIds = null;
    private int $chunk = AppConfig::DEFAULT_CHUNK_FETCH;

    public function __construct(array $selectedDatasets)
    {
        $datasetIds = array_values(array_filter($selectedDatasets, 'is_numeric', ARRAY_FILTER_USE_KEY))
            ?: array_values($selectedDatasets);

        $this->query = Image::whereIn('dataset_id', $datasetIds)
            ->select(['id', 'filename', 'dataset_folder', 'dataset_id', 'width', 'height']);
        /*$this->query = DB::table("images")
            ->whereIn('dataset_id', $datasetIds)
            ->select(['id', 'filename', 'dataset_folder', 'dataset_id', 'width', 'height']);*/
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

    public function chunkByAnnotations(?int $size, bool $randomize, Closure $callback): void
    {
        $size = $size > 0 ? $size : $this->chunk;
        $lastId = 0;
        $imageIds = $this->query->pluck('id')->toArray();
        do {
            $annotations = $this->fetchAnnotationsChunk($size, $lastId, $imageIds, $randomize);
            if (empty($annotations)) {
                break;
            }
            $lastGroup = end($annotations);
            $lastAnnotation = end($lastGroup);
            $lastId = $lastAnnotation['id'];

            $annotationsCount = 0;
            foreach ($annotations as $group) {
                $annotationsCount += count($group);
            }

            // Get images
            $imageIdsForChunk = array_unique(array_column(array_merge(...$annotations), 'image_id'));
            $images = (clone $this->query)->whereIn('id', $imageIdsForChunk)->get();


            // Attach annotations to images
            $images = $this->attachAnnotationsAndClasses($images, $annotations);

            $callback($images);

            unset($annotations, $images);
            gc_collect_cycles();

        } while ($annotationsCount === $size);
    }


    public function get()
    {
        // Fetch either paginated or non-paginated images
        $images = $this->perPage
            ? $this->query->paginate($this->perPage)
            : $this->query->get();

        // Attach annotations and classes, and preserve pagination if it exists
        $this->attachAnnotationsAndClasses($images);
        return $images;
    }

    private function attachAnnotationsAndClasses($images, $annotations = null): LengthAwarePaginator|array
    {
        // Handle both paginated and non-paginated results
        $isPaginator = method_exists($images, 'getCollection');
        $collection = $isPaginator ? $images->getCollection() : collect($images);
        $imageIds = $collection->pluck('id')->toArray();

        if (empty($imageIds)) {
            return $images;
        }

        if (is_null($annotations)) {
            $annotations = $this->fetchAnnotations($imageIds);
        }

        // Fetch annotation classes based on annotation class IDs
        $usedClassIds = collect($annotations)->flatten(1)->pluck('annotation_class_id')->unique()->toArray();
        $annotationClasses = $this->fetchAnnotationClasses($usedClassIds);

        Util::logStart("collection->transform");

        $collection->each(function ($image) use ($annotations, $annotationClasses) {
            $imageAnnotations = $annotations[$image->id];

            foreach ($imageAnnotations as &$annotation) {
                $annotation['class'] = $annotationClasses[$annotation['annotation_class_id']];
            }

            $image->setRelation('annotations', collect($imageAnnotations));
        });
        Util::logEnd("collection->transform");
        // Return the images with annotations and classes
        if ($isPaginator) {
            return $images->setCollection($collection);
        }

        return $collection->toArray();
    }


    private function fetchAnnotations(array $imageIds): array
    {
        return DB::table('annotation_data')
            ->select('id', 'image_id', 'annotation_class_id', 'svg_path')
            ->whereIn('image_id', $imageIds)
            ->when(!empty($this->classIds), fn($query) => $query->whereIn('annotation_class_id', $this->classIds))
            ->get()
            ->map(function ($annotation) {
                return (array) $annotation;
            })
            ->groupBy('image_id')
            ->toArray();
    }

    private function fetchAnnotationsChunk(int $size, int $lastId, array $imageIds, bool $randomize = false): array
    {
        return DB::table('annotation_data')
            ->select('id', 'image_id', 'x', 'y', 'width', 'height', 'annotation_class_id', 'segmentation')
            ->when(!empty($this->classIds), fn($query) => $query->whereIn('annotation_class_id', $this->classIds))
            ->where('id', '>', $lastId)
            ->whereIn('image_id', $imageIds)
            ->when($randomize, fn($query) => $query->inRandomOrder(), fn($query) => $query->orderBy('id'))
            ->limit($size)
            ->get()
            ->map(function ($annotation) {
                $annotation->segmentation = json_decode($annotation->segmentation, true);
                return (array) $annotation;
            })
            ->groupBy('image_id')
            ->toArray();
    }

    private function fetchAnnotationClasses(array $classIds): array
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
