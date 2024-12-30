<?php

namespace Tests\Feature;

use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use App\ImportService\Strategies\NewDatasetStrategy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;


class DatasetImportTest extends TestCase
{
    use RefreshDatabase;
    public $datasetName = 'test_dataset.zip';
    public $uniqueName = 'valid_bbox.zip';

    public $metadata = [
        "1" => [
            "metadataValues" => [
                "2",
                "3"
            ]
        ],
        "2" => [
            "metadataValues" => [
                "4",
            ]
        ]
    ];
    public $categories = [
        "1",
        "2"
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('storage');
        config(['filesystems.disks.datasets.root' => Storage::disk('storage')->path('app/public/datasets')]);
    }

    public function prepareZipFile()
    {
        $mockZipPath = base_path("tests/Data/{$this->uniqueName}");
        Storage::disk('storage')->put(AppConfig::LIVEWIRE_TMP_PATH.$this->uniqueName, file_get_contents($mockZipPath));
        $temporaryFile = new UploadedFile(
            Storage::disk('storage')->path(AppConfig::LIVEWIRE_TMP_PATH.$this->uniqueName),
            $this->uniqueName,
            'application/zip',
            null,
            true
        );
        return $temporaryFile;
    }
    public function preparePayload(array $payloadOverride)
    {
        return [
            'display_name' => $payloadOverride['display_name'] ?? pathinfo($this->datasetName, PATHINFO_FILENAME),
            'unique_name' => $payloadOverride['unique_name'] ?? pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $payloadOverride['format'] ?? AppConfig::ANNOTATION_FORMATS_INFO['yolo']['name'],
            'metadata' => $payloadOverride['metadata'] ?? $this->metadata,
            'technique' => $payloadOverride['technique'] ?? AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'],
            'categories' => $payloadOverride['categories'] ?? $this->categories,
            'description' => $payloadOverride['description'] ?? 'Test dataset with folder structure',
        ];
    }

    public function extractZip()
    {
        $zipExtraction = app(ZipManager::class);
        $temporaryFile = $this->prepareZipFile();
        return $zipExtraction->processZipFile($temporaryFile);
    }

    public function prepare()
    {

    }
    public function test_processes_a_zip_file_and_creates_folders()
    {
        // 1. Prepare a mock ZIP file
        $this->uniqueName = 'valid_bbox.zip';

        // 2. Extract the ZIP file
        $this->assertTrue($this->extractZip()->isSuccessful());

        // 3. Instantiate the import service
        $importService = app(ImportService::class, ['strategy' => new NewDatasetStrategy()]);
        $payload = $this->preparePayload([]);
        $datasetImported = $importService->handleImport($payload);

        // 4. Assert the dataset was successfully imported
        $this->assertTrue($datasetImported->isSuccessful());

        // 5. Assert the dataset folders were created
        $datasetFolder = "app/public/datasets/".pathinfo($this->uniqueName, PATHINFO_FILENAME);
        $this->assertDirectoryExists(Storage::disk('storage')->path($datasetFolder), "Dataset folder does not exist.");
        $this->assertDirectoryExists(Storage::disk('storage')->path("{$datasetFolder}/thumbnails"), "Thumbnails folder does not exist.");
        $this->assertDirectoryExists(Storage::disk('storage')->path("{$datasetFolder}/class-images"), "Class images folder does not exist.");
        $this->assertDirectoryExists(Storage::disk('storage')->path("{$datasetFolder}/full-images"), "Full images folder does not exist.");
    }

    public function test_wrong_zip_structure()
    {
        // 1. Prepare a mock ZIP file
        $this->uniqueName = 'BBOX_zipWrong.zip';
        $this->assertTrue($this->extractZip()->isSuccessful());


        // 2. Instantiate the import service
        $importService = app(ImportService::class, ['strategy' => new NewDatasetStrategy()]);

        $payload = $this->preparePayload([]);
        $result = $importService->handleImport($payload);

        // 3. Assert the dataset was not successfully imported
        $this->assertTrue($result->message == "Zip structure issues found");
        $this->assertFalse($result->isSuccessful());
    }

    public function test_wrong_annotations()
    {
            // 1. Prepare a mock ZIP file
        $this->uniqueName = 'BBOX_annot_wrong.zip';
        $this->assertTrue($this->extractZip()->isSuccessful());

        // 2. Instantiate the import service
        $importService = app(ImportService::class, ['strategy' => new NewDatasetStrategy()]);

        $payload = $this->preparePayload([]);
        $result = $importService->handleImport($payload);

        // 3. Assert the dataset was not successfully imported
        $this->assertTrue($result->message == "Annotation issues found");
        $this->assertFalse($result->isSuccessful()); }
}
