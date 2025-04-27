<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }

    #[PreserveGlobalState(false)] #[RunInSeparateProcess] public function test_download_zip_dataset()
    {
        $this->withoutOutputBuffering();
        // Disable output buffering during the test
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Setup test file
        $sourceDir = app_path('../tests/Data/valid_bbox.zip');
        $destinationDir = Storage::path('app/public/datasets/valid_bbox.zip');
        $destinationDirDir = dirname($destinationDir);

        // Ensure the destination directory exists and copy the file
        File::ensureDirectoryExists($destinationDirDir);
        File::copy($sourceDir, $destinationDir);

        // Simulate session with file path
        $this->withSession(['download_file_path' => $destinationDir]);

        // Assert that the file exists before download
        $this->assertFileExists($destinationDir);

        // Trigger the download route
        $response = $this->get(route('download.file'));

        // Assertions
        $response->assertStatus(200); // Ensure the download response is successful

        // Verify that the file was deleted after download
        $this->assertFileDoesNotExist($destinationDir);
    }

}
