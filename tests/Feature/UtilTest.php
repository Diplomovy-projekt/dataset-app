<?php

namespace Tests\Feature;

use App\Configs\AppConfig;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\Util;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UtilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }
    public function test_get_dataset_path_with_model_returns_relative_path()
    {
        $dataset = Dataset::factory()->create([
            'unique_name' => 'model-dataset',
            'is_public' => true,
        ]);

        $path = Util::getDatasetPath($dataset);
        $this->assertSame(AppConfig::DATASETS_PATH['public'] . 'model-dataset/', $path);
    }

    public function test_get_dataset_path_with_unique_name_string_returns_relative_path()
    {
        $dataset = Dataset::factory()->create([
            'unique_name' => 'string-dataset',
            'is_public' => false,
        ]);

        $path = Util::getDatasetPath('string-dataset');
        $this->assertSame(AppConfig::DATASETS_PATH['private'] . 'string-dataset/', $path);
    }

    public function test_get_dataset_path_with_id_and_absolute_true_returns_absolute_path()
    {
        $dataset = Dataset::factory()->create([
            'unique_name' => 'absolute-dataset',
            'is_public' => true,
        ]);

        $path = Util::getDatasetPath($dataset->id, true);
        $this->assertSame(Storage::path(AppConfig::DATASETS_PATH['public'] . 'absolute-dataset/'), $path);
    }

    public function test_get_image_size_stats_with_image_ids()
    {
        $images = Image::factory()->count(3)->create()->each(function ($image, $index) {
            // Manually adjust the width and height for each image after creation
            if ($index == 0) {
                $image->update(['width' => 100, 'height' => 200]);
            } elseif ($index == 1) {
                $image->update(['width' => 300, 'height' => 400]);
            } elseif ($index == 2) {
                $image->update(['width' => 200, 'height' => 100]);
            }
        });


        $ids = $images->pluck('id')->toArray();

        $result = Util::getImageSizeStats($ids, true);

        $this->assertSame('200x200', $result['median']);
        $this->assertSame('100x100', $result['min']);
        $this->assertSame('300x400', $result['max']);
    }

    public function test_get_image_size_stats_with_dataset_ids()
    {
        $dataset = Dataset::factory()->create();
        Image::factory()->create(['dataset_id' => $dataset->id, 'width' => 50, 'height' => 150]);
        Image::factory()->create(['dataset_id' => $dataset->id, 'width' => 150, 'height' => 50]);
        Image::factory()->create(['dataset_id' => $dataset->id, 'width' => 100, 'height' => 100]);

        $result = Util::getImageSizeStats([$dataset->id]);

        $this->assertSame('100x100', $result['median']);
        $this->assertSame('50x50', $result['min']);
        $this->assertSame('150x150', $result['max']);
    }

    public function test_get_image_size_stats_with_empty_ids_returns_zeros()
    {
        $result = Util::getImageSizeStats([]);

        $this->assertSame([
            'median' => '0x0',
            'min' => '0x0',
            'max' => '0x0',
        ], $result);
    }

    public function test_get_image_size_stats_with_non_existent_ids_returns_zeros()
    {
        $nonExistentIds = [9999, 8888, 7777];

        $result = Util::getImageSizeStats($nonExistentIds, true);

        $this->assertSame([
            'median' => '0x0',
            'min' => '0x0',
            'max' => '0x0',
        ], $result);
    }

}
