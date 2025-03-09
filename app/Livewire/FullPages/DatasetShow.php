<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\ExportService\ExportService;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\Util;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DatasetShow extends Component
{
    use WithPagination, ImageRendering;
    public $uniqueName;
    public $dataset;
    public $searchTerm;
    public Collection $metadata;
    public Collection $categories;
    public array $toggleClasses;
    public string $modalStyle;
    public array $selectedImages = [];
    public int $perPage = 25;

    #[Computed(persist: true, seconds: 900)]
    public function paginatedImages()
    {
        // Fetch images as LengthAwarePaginator
        $imagesPaginator = $this->fetchImages();

        // Get memory usage before processing the LengthAwarePaginator
        $beforePaginatorMemory = memory_get_usage();

        // Serialize the paginator to get its memory size (before converting to array)
        $serializedPaginator = serialize($imagesPaginator);

        // Get memory usage after serializing the paginator
        $afterPaginatorMemory = memory_get_usage();

        // Calculate the memory size difference for the paginator in bytes
        $paginatorMemorySize = $afterPaginatorMemory - $beforePaginatorMemory;

        // Convert to MB
        $paginatorMemorySizeMB = $paginatorMemorySize / 1048576;

        echo "LengthAwarePaginator Memory Size (Before Conversion): " . round($paginatorMemorySizeMB, 2) . " MB\n";

        // Prepare images for rendering (with LengthAwarePaginator, this is the first case)
        $preparedImagesPaginator = $this->prepareImagesForSvgRendering($imagesPaginator);

        // Get memory usage before serializing the prepared images (LengthAwarePaginator)
        $beforePreparedPaginatorMemory = memory_get_usage();

        // Serialize the prepared images to get its memory size
        $serializedPreparedPaginator = serialize($preparedImagesPaginator);

        // Get memory usage after serializing the prepared images
        $afterPreparedPaginatorMemory = memory_get_usage();

        // Calculate the memory size difference for the prepared images (LengthAwarePaginator) in bytes
        $preparedPaginatorMemorySize = $afterPreparedPaginatorMemory - $beforePreparedPaginatorMemory;

        // Convert to MB
        $preparedPaginatorMemorySizeMB = $preparedPaginatorMemorySize / 1048576;

        echo "Prepared Images (LengthAwarePaginator) Memory Size: " . round($preparedPaginatorMemorySizeMB, 2) . " MB\n";

        // Convert LengthAwarePaginator to array for the second case (images + annotations)
        $imagesArray = $imagesPaginator->toArray();

        // Get memory usage before processing the array (second part)
        $beforeArrayMemory = memory_get_usage();

        // Serialize the images array to get its memory size (second case)
        $serializedArray = serialize($imagesArray);

        // Get memory usage after serializing the images array
        $afterArrayMemory = memory_get_usage();

        // Calculate the memory size difference for the array in bytes
        $arrayMemorySize = $afterArrayMemory - $beforeArrayMemory;

        // Convert to MB
        $arrayMemorySizeMB = $arrayMemorySize / 1048576;

        echo "Array Memory Size (with images and annotations): " . round($arrayMemorySizeMB, 2) . " MB\n";

        // Prepare images for rendering (with the array of images, this is the second case)
        $preparedImagesArray = $this->prepareImagesForSvgRendering($imagesPaginator)->toArray();

        // Get memory usage before serializing the prepared images (Array)
        $beforePreparedArrayMemory = memory_get_usage();

        // Serialize the prepared images to get its memory size (Array)
        $serializedPreparedArray = serialize($preparedImagesArray);

        // Get memory usage after serializing the prepared images
        $afterPreparedArrayMemory = memory_get_usage();

        // Calculate the memory size difference for the prepared images (Array) in bytes
        $preparedArrayMemorySize = $afterPreparedArrayMemory - $beforePreparedArrayMemory;

        // Convert to MB
        $preparedArrayMemorySizeMB = $preparedArrayMemorySize / 1048576;

        echo "Prepared Images (Array) Memory Size: " . round($preparedArrayMemorySizeMB, 2) . " MB\n";

        /*dd(
            "LengthAwarePaginator Memory Size (Before Conversion): " . round($paginatorMemorySizeMB, 2) . " MB\n",
            "Prepared Images (LengthAwarePaginator) Memory Size: " . round($preparedPaginatorMemorySizeMB, 2) . " MB\n",
            "Array Memory Size (with images and annotations): " . round($arrayMemorySizeMB, 2) . " MB\n",
            "Prepared Images (Array) Memory Size: " . round($preparedArrayMemorySizeMB, 2) . " MB\n"
        );*/
        // Return the final prepared images (either as paginator or array)
        return $preparedImagesPaginator;  // Or you can return $preparedImagesPaginator based on your needs
    }

    public function mount()
    {
        Util::logStart("dataset show MOUNT");
        $dataset = Dataset::where('unique_name', $this->uniqueName)->with(['classes'])->first();
        if (!$dataset) {
            return redirect()->route('dataset.index');
        }

        $dataset->stats = $dataset->getStats();
        foreach ($dataset->classes as $class) {
            $datasetPath = Util::getDatasetPath($dataset);
            $firstFile = collect(Storage::files($datasetPath . AppConfig::CLASS_IMG_FOLDER . $class->id))
                ->first();
            $class->image = [
                'filename' => pathinfo($firstFile, PATHINFO_BASENAME),
                'folder' => AppConfig::CLASS_IMG_FOLDER . $class->id,
            ];
        }

        $this->dataset = $dataset->toArray();
        $this->toggleClasses = $dataset['classes']->toArray();
        $this->metadata = $dataset->metadataGroupedByType();
        $this->categories = $dataset->categories()->get();
        Util::logEnd("dataset show MOUNT");

    }

    public function updatedPerPage()
    {
        unset($this->paginatedImages);
    }

    public function search()
    {
        $this->searchTerm = trim($this->searchTerm);
        unset($this->paginatedImages);
    }
    private function fetchImages()
    {
        if ($this->searchTerm) {
            return Image::where('dataset_id', $this->dataset['id'])->where('filename', 'like', '%' . $this->searchTerm . '%')->with(['annotations.class'])->paginate($this->perPage);
        }
        else {
            return Image::where('dataset_id', $this->dataset['id'])->with(['annotations.class'])->paginate($this->perPage);
        }
    }
    public function deleteDataset(DatasetActions $datasetService): void
    {
        $result = $datasetService->deleteDataset($this->uniqueName);
        if($result->isSuccessful()){
            redirect()->route('profile');
        }
    }

    public function deleteImages(DatasetActions $datasetService)
    {
        $result = $datasetService->deleteImages($this->uniqueName, $this->selectedImages);
        if($result->isSuccessful()){
            $this->mount();
        }
    }

    public function cacheQuery($id)
    {
        Util::logStart("dataset show CACHE QUERY");
        $query = Image::where('dataset_id', $id)->with('annotations.class');
        $payload['query'] = \EloquentSerialize::serialize($query);
        $payload['datasets'] = [$id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(30));
        Util::logEnd("dataset show CACHE QUERY");
        Util::logStart("dataset show DISPATCH");
        $this->dispatch('store-download-token', token: $token);
        Util::logEnd("dataset show DISPATCH");
    }

}
