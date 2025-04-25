<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Components\DownloadDataset;
use App\Models\Dataset;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;
use ZipArchive;

class ExportDatasetTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage();
    }

    public function test_dataset_export(): void
    {
        Queue::fake();
        $payload['datasets'] = [Dataset::first()->id];

        $token = Str::random(32);
        Cache::put("download_query_{$token}", $payload, now()->addMinutes(2));

        $component = Livewire::test(DownloadDataset::class, ['exportFormat' => 'yolo'])
            ->call('storeDownloadToken', $token)
            ->call('startProcessing');

        $folder = $component->get('exportDataset'); // Assuming this returns the folder name with .zip
        $folderWithoutZip = Str::replaceLast('.zip', '', $folder); // Remove '.zip' from the folder name

        // Path to the ZIP file
        $zipPath = Storage::path("app/public/datasets/{$folder}");

        // Assert the ZIP file exists
        Storage::assertExists("app/public/datasets/{$folder}");

        // Open the ZIP file
        $zip = new ZipArchive;
        $result = $zip->open($zipPath);

        // Ensure the ZIP file opened successfully
        $this->assertTrue($result === true, 'Unable to open ZIP file.');

        // Check for the 'images' directory and 3 files inside it
        $imagesFiles = $this->getZipFiles($zip, 'images/');
        $this->assertCount(3, $imagesFiles, 'There should be exactly 3 files in the images directory.');

        // Check for the 'labels' directory and 3 files inside it
        $labelsFiles = $this->getZipFiles($zip, 'labels/');
        $this->assertCount(3, $labelsFiles, 'There should be exactly 3 files in the labels directory.');

        // Check for the 'data.yaml' file
        $this->assertTrue($zip->locateName('data.yaml') !== false, 'data.yaml file not found in the ZIP.');

        // Close the ZIP file
        $zip->close();
    }

    private function getZipFiles(ZipArchive $zip, string $directory)
    {
        $files = [];

        // Iterate through the files in the ZIP and check for those in the specified directory
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);

            // Normalize the file name to use forward slashes
            $fileName = str_replace('\\', '/', $fileName); // Convert backslashes to forward slashes

            // Normalize the directory to use forward slashes and ensure proper comparison
            $directory = rtrim($directory, '/') . '/';

            // Check if the file is within the desired directory (prefix check)
            if (strpos($fileName, $directory) === 0) {
                $files[] = $fileName;
            }
        }

        return $files;
    }

}
