<?php

namespace Tests\Feature;

use App\Configs\AppConfig;
use App\FileManagement\ZipManager;
use App\ImportService\ImportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatasetImportTest extends TestCase
{
    use RefreshDatabase;

    public $datasetName = 'test_dataset.zip';
    public $uniqueName = 'valid_bbox.zip';

    public $metadata = [2,3,4];

    public $categories = ["1", "2"];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }
    public function test_admin_user_can_upload_dataset()
    {
        // 1. Create and act as admin user
        $admin = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($admin);

        // 2. Prepare and extract zip
        $this->uniqueName = 'valid_bbox.zip';
        $this->assertTrue($this->extractZip()->isSuccessful());

        // 3. Import dataset
        $importService = app(ImportService::class);
        $result = $importService->handleImport($this->preparePayload([]));
        // 4. Assert import success
        $this->assertTrue($result->isSuccessful());

        // 5. Assert dataset folders exist
        $basePath = Storage::disk('storage')->path('app/private/datasets/' . pathinfo($this->uniqueName, PATHINFO_FILENAME));
        $this->assertDirectoryExists("{$basePath}");
        $this->assertDirectoryExists("{$basePath}/thumbnails");
        $this->assertDirectoryExists("{$basePath}/class-images");
        $this->assertDirectoryExists("{$basePath}/full-images");
        $this->assertDatabaseHas('datasets', [
            'unique_name' => pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'user_id' => $admin->id,
        ]);
    }

    public function prepareZipFile()
    {
        $mockZipPath = base_path("tests/Data/{$this->uniqueName}");
        Storage::disk('storage')->put(AppConfig::LIVEWIRE_TMP_PATH . $this->uniqueName, file_get_contents($mockZipPath));
        return new UploadedFile(
            Storage::disk('storage')->path(AppConfig::LIVEWIRE_TMP_PATH . $this->uniqueName),
            $this->uniqueName,
            'application/zip',
            null,
            true
        );
    }

    public function preparePayload(array $override = [])
    {
        return [
            'display_name' => $override['display_name'] ?? pathinfo($this->datasetName, PATHINFO_FILENAME),
            'unique_name' => $override['unique_name'] ?? pathinfo($this->uniqueName, PATHINFO_FILENAME),
            'format' => $override['format'] ?? AppConfig::ANNOTATION_FORMATS_INFO['yolo']['name'],
            'metadata' => $override['metadata'] ?? $this->metadata,
            'technique' => $override['technique'] ?? AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'],
            'categories' => $override['categories'] ?? $this->categories,
            'description' => $override['description'] ?? 'Test dataset with folder structure',
        ];
    }

    public function extractZip()
    {
        $zipManager = app(ZipManager::class);
        return $zipManager->processZipFile($this->prepareZipFile());
    }

}
