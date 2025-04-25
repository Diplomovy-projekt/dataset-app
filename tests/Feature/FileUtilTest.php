<?php

namespace Tests\Feature;

use App\Utils\FileUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUtilTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }

    public function test_delete_empty_directories_removes_empty_ones_and_keeps_non_empty()
    {
        Storage::makeDirectory('a/b/empty');
        Storage::makeDirectory('a/b/not-empty');
        Storage::put('a/b/not-empty/file.txt', 'data');

        FileUtil::deleteEmptyDirectories('a');

        $this->assertFalse(Storage::exists('a/b/empty'));
        $this->assertTrue(Storage::exists('a/b/not-empty'));
        $this->assertTrue(Storage::exists('a'));
    }

    public function test_ensure_folder_exists_creates_directory_for_file_path_using_storage()
    {
        $path = 'some/dir/file.txt';
        $this->assertFalse(Storage::exists('some/dir'));

        FileUtil::ensureFolderExists($path, true);

        $this->assertTrue(Storage::exists('some/dir'));
    }

    public function test_ensure_folder_exists_creates_directory_for_folder_path_using_storage()
    {
        $path = 'another/dir/path/';
        $this->assertFalse(Storage::exists('another/dir/path'));

        FileUtil::ensureFolderExists($path, true);

        $this->assertTrue(Storage::exists('another/dir/path'));
    }

    public function test_ensure_folder_exists_creates_directory_for_file_path_without_storage()
    {
        $path = storage_path('app/test/dir/file.txt');
        $dirPath = dirname($path);
        File::deleteDirectory($dirPath);

        $this->assertFalse(File::exists($dirPath));

        FileUtil::ensureFolderExists($path);

        $this->assertTrue(File::exists($dirPath));
    }

    public function test_ensure_folder_exists_creates_directory_for_folder_path_without_storage()
    {
        $path = storage_path('app/test/dir/folder/');
        File::deleteDirectory($path);

        $this->assertFalse(File::exists($path));

        FileUtil::ensureFolderExists($path);

        $this->assertTrue(File::exists($path));
    }

    public function test_add_unique_suffix_with_custom_suffix()
    {
        $result = FileUtil::addUniqueSuffix('folder/test.jpg', '_v2');
        $this->assertSame('folder/test_v2.jpg', $result);
    }

    public function test_add_unique_suffix_with_generated_suffix()
    {
        $result = FileUtil::addUniqueSuffix('folder/test.jpg');
        $this->assertStringStartsWith('folder/test_da_', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    public function test_add_unique_suffix_with_empty_filename_returns_empty_string()
    {
        $this->assertSame('', FileUtil::addUniqueSuffix(''));
    }

}
