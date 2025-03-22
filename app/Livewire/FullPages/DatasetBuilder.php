<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\ImageService\ImageRendering;
use App\Models\AnnotationClass;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\Image;
use App\Models\MetadataValue;
use App\Utils\ImageQuery;
use App\Utils\QueryUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class DatasetBuilder extends Component
{
    use ImageRendering, WithPagination, WithoutUrlPagination;
    #[Locked]
    public $currentStage = 0;
    #[Locked]
    public $completedStages = [];
    #[Locked]
    public $stageData = [
        1 => [
            'title' => 'Annotation Technique',
            'description' => 'Select the annotation technique you want to use for your project.',
            'component' => 'builder.annotation-technique-stage',
            'method' => 'annotationTechniqueStage',
        ],
        2 => [
            'title' => 'Select Categories',
            'description' => 'Choose the categories relevant to your dataset for this project.',
            'component' => 'builder.categories-stage',
            'method' => 'categoriesStage',
        ],
        3 => [
            'title' => 'Dataset Origin',
            'description' => 'Specify the source or origin of the dataset you want to use.',
            'component' => 'builder.origin-stage',
            'method' => 'originStage',
        ],
        4 => [
            'title' => 'Datasets Selection',
            'description' => 'Select the specific datasets required for your project.',
            'component' => 'builder.datasets-stage',
            'method' => 'datasetsStage',
        ],
        5 => [
            'title' => 'Final Selection',
            'description' => 'Select any images that you wish to EXCLUDE from your final dataset',
            'component' => 'builder.final-stage',
            'method' => 'finalStage',
        ],
        6 => [
            'title' => 'Download',
            'description' => 'Download the final prepared dataset for your project.',
            'component' => 'builder.download-stage',
            'method' => 'downloadFilter',
        ],
    ];

    public string $selectedAnnotationTechnique = AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'];
    #[Locked]
    public $categories = [];
    public $selectedCategories = [];
    #[Locked]
    public $metadataValues = [];
    public $selectedMetadataValues = [];
    public $skipTypes = [];
    #[Locked]
    public array $datasetIds = [];
    public array $selectedDatasets = [];
    public $selectedClasses = [];
    private $images = [];
    public $selectedImages = [];
    public $finalDataset = [
        'stats' => [],
        'metadataValues' => [],
        'categories' => [],
    ];
    public array $polygonDatasetsStats = [];
    public array $allDatasetsStats = [];
    public int $perPage = 25;

    #[Computed]
    public function paginatedImages()
    {
        $images = $this->imagesQuery($this->perPage);
        return $this->prepareImagesForSvgRendering($images);
    }

    #[Computed]
    public function paginatedDatasets()
    {
        Util::logStart("BUILDER paginatedDatasets");
        $dat = Dataset::approved()
            ->whereIn('id', $this->datasetIds)
            ->when(
                $this->selectedAnnotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON'],
                fn($query) => $query->where('annotation_technique', AppConfig::ANNOTATION_TECHNIQUES['POLYGON'])
            )
            ->with([
                'classes',
                'metadataValues',
                'categories',
                'images' => fn($query) => $query->limit(1)->select(['id', 'filename', 'dataset_folder', 'dataset_id', 'width', 'height'])->with([
                    'annotations' => fn($q) => $q->select(['id', 'image_id', 'x', 'y', 'width', 'height', 'annotation_class_id', 'segmentation'])
                        ->with([
                            'class' => fn($q) => $q->select(['id', 'name', 'rgb'])->get()->map(fn($c) => $c->toArray()) // Convert to array
                        ])
                ]),
            ])
            ->paginate(5)
            ->through(fn($dataset) => $this->processDataset($dataset));
        Util::logEnd("BUILDER paginatedDatasets");
        return $dat;
    }
    private function processDataset($dataset)
    {
        $dataset->stats = $dataset->getStats();
        $dataset->image_stats = Util::getImageSizeStats([$dataset->id]);

        if ($dataset->images->isEmpty()) {
            $dataset->thumbnail = "placeholder-image.png";
        } else {
            $dataset->setRelation('images', $this->prepareImagesForSvgRendering($dataset->images->first()));
        }

        return $dataset;
    }
    #[On('add-selected')]
    public function receiveSelected($selectedClasses, $datasetId)
    {
        if (!isset($this->selectedClasses[$datasetId])) {
            $this->selectedClasses[$datasetId] = $selectedClasses;
        }
    }
    public function render()
    {
        /*$this->datasets = Dataset::approved()->with(['classes', 'metadataValues', 'categories'])
            ->orderBy('id', 'desc')
            ->limit(2) // Get only the last two
            ->get();

        foreach ($this->datasets as $dataset) {
            $dataset->stats = $dataset->getStats();
            $dataset->image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($dataset->unique_name))[0];
            $dataset->image_stats = Util::getImageSizeStats([$dataset->id]);
            $dataset->image = $dataset->image->toArray();
        }
        $this->datasets = $this->datasets->toArray();*/
        return view('livewire.full-pages.dataset-builder');
    }

    public function updatedPerPage()
    {
        unset($this->images);
    }

    public function nextStage()
    {
        if($this->currentStage < count($this->stageData)){
            $this->completedStages[] = $this->currentStage;
            $this->currentStage++;
            $this->applyStageFilters();
        }
    }

    public function previousStage()
    {
        if($this->currentStage > 1){
            $this->currentStage--;
            $this->completedStages =array_values(array_diff($this->completedStages, [$this->currentStage]));
            $this->applyStageFilters();
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function applyStageFilters()
    {
        $stageDetails = $this->stageData[$this->currentStage] ?? null;

        if ($stageDetails && method_exists($this, $stageDetails['method'])) {
            $method = $stageDetails['method'];

            // Resolve method dependencies
            $reflection = new \ReflectionMethod($this, $method);
            $parameters = $reflection->getParameters();
            $dependencies = array_map(fn($param) => app($param->getType()?->getName()), $parameters);

            $this->{$method}(...$dependencies);
        }
    }

    public function annotationTechniqueStage()
    {
        // Get ids of datasets that have the selected annotation technique polygon
        $polygonDatasetIds = Dataset::approved()->where('annotation_technique', AppConfig::ANNOTATION_TECHNIQUES['POLYGON'])
            ->pluck('id')
            ->all();
        $this->polygonDatasetsStats = QueryUtil::getDatasetCounts($polygonDatasetIds);

        $this->allDatasetsStats = QueryUtil::getDatasetCounts();

    }
    private function categoriesStage()
    {
        $this->categories = DatasetCategory::getAllUniqueCategories();
        // If selectedAnnotationTechnique is set to polygon, remove the categories  that are linked to datasets with no polygon annotations
        if ($this->selectedAnnotationTechnique === AppConfig::ANNOTATION_TECHNIQUES['POLYGON']) {
            $this->categories = $this->categories->filter(function ($category) {
                return Dataset::approved()->whereRelation('categories', 'category_id', $category->id)
                    ->where('annotation_technique', 'Polygon')
                    ->exists();
            });
        }
        $this->categories = $this->categories->map(function ($category) {
            $datasetUniqueName = Dataset::approved()->whereRelation('categories', 'category_id', $category->id)->pluck('unique_name')->first();
            $image = $this->prepareImagesForSvgRendering(QueryUtil::getFirstImage($datasetUniqueName))[0];
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $image,
            ];
        })->toArray();
    }

    private function originStage()
    {
        $this->metadataValues = DatasetMetadata::getGroupedMetadataByCategories($this->selectedCategories);
        $this->datasetsStage();
    }

    public function updatedSkipTypes()
    {
        $this->datasetsStage();
    }
    public function updatedSelectedMetadataValues()
    {
        $this->datasetsStage();
    }
    private function datasetsStage()
    {
        Util::logStart('datasets_stage');
        $this->datasetIds = [];
        // Get all explicitly selected metadata values
        $explicitlyIncluded = array_keys(array_filter($this->selectedMetadataValues));
        // Get all metadata values from skipped types (treat them as included)
        $skippedValues = MetadataValue::whereIn('metadata_type_id', $this->skipTypes)
            ->pluck('id')
            ->all();
        // Combine both sets of values
        $metadataValueIds = array_unique(array_merge($explicitlyIncluded, $skippedValues));

        // Query datasets that match at least one metadata value
        $datasetMetadataIds = DatasetMetadata::whereIn('metadata_value_id', $metadataValueIds)
            ->whereHas('dataset', function ($query) {
                $query->approved();
            })
            ->whereHas('dataset.categories', function ($query) {
                $query->whereIn('category_id', $this->selectedCategories);
            })
            ->distinct()
            ->pluck('dataset_id');

        // Query datasets that have no metadata but belong to selected categories
        $datasetsWithoutMetadata = Dataset::approved()->whereDoesntHave('metadataValues')
            ->whereHas('categories', function ($query) {
                $query->whereIn('category_id', $this->selectedCategories);
            })
            ->pluck('id');

        // Merge both sets of datasets
        $this->datasetIds = $datasetMetadataIds->merge($datasetsWithoutMetadata)->unique()->toArray();

        $this->selectedImages = [];
        Util::logEnd('datasets_stage');
    }

    private function finalStage()
    {
        $this->selectedImages = [];
        unset($this->paginatedImages);
        $this->finalDataset['stats'] = $this->getCustomStats();
        $this->finalDataset['image_stats'] = Util::getImageSizeStats($this->imagesQuery()->pluck('id')->toArray(), true);
    }

    private function imagesQuery($perPage = null)
    {
        $classIds = $this->getSelectedClassesForSelectedDatasets();

        return ImageQuery::forDatasets(array_keys(array_filter($this->selectedDatasets)))
            ->excludeImages($this->selectedImages)
            ->filterByClassIds($classIds)
            ->perPage($perPage)
            ->get();
    }

    private function downloadFilter()
    {
        $this->images = $this->imagesQuery()->toArray();

        $this->finalDataset['stats'] = $this->getCustomStats();
        $this->finalDataset['image_stats'] = Util::getImageSizeStats(array_column($this->images, 'id'), true);
        $datasetIds = array_keys(array_filter($this->selectedDatasets));
        $this->finalDataset['categories'] = Category::whereIn('id', function ($query) use ($datasetIds) {
            $query->select('category_id')
                ->from('dataset_categories')
                ->whereIn('dataset_id', $datasetIds);
        })->get(['id', 'name'])->toArray();

        $this->finalDataset['metadataValues'] = MetadataValue::whereIn('id', function ($query) use ($datasetIds) {
            $query->select('metadata_value_id')
                ->from('dataset_metadata')
                ->whereIn('dataset_id', $datasetIds);
        })->get(['id', 'value'])->toArray();
    }

    #[Renderless]
    public function cacheQuery()
    {
        $payload['classIds'] = $this->getSelectedClassesForSelectedDatasets();
        $payload['selectedImages'] = $this->selectedImages;
        $payload['datasets'] = array_keys(array_filter($this->selectedDatasets));

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));

        $this->dispatch('store-download-token', token: $token);
    }

    private function getSelectedClassesForSelectedDatasets(): array
    {
        $eligiblePerDataset = array_intersect_key($this->selectedClasses, array_filter($this->selectedDatasets));
        // Flatten the arrays and merge them
        $classIds = [];
        foreach($eligiblePerDataset as $datasetId => $classes) {
            $classIds = $classIds + array_filter($classes);
        }
        return array_keys($classIds);
    }

    private function getCustomStats()
    {
        $images = $this->imagesQuery()->toArray();
        $classCount = AnnotationClass::whereIn('id', $this->getSelectedClassesForSelectedDatasets())
            ->get(['name'])
            ->unique('name')
            ->count();
        return [
            'numImages' => count($images),
            'numClasses' => $classCount,
            'numAnnotations' => array_sum(array_map(fn($image) => count($image['annotations']), $images)),
        ];
    }
}
