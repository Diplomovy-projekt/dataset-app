<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    public function getUser($role = 'admin')
    {
        return \App\Models\User::where('role', $role)->first();
    }
    public function setUpStorage($setUpDataset = true, $visibility = 'public')
    {
        $this->seed(DatabaseSeeder::class);

        $path = storage_path('framework/testing/disks/storage');
        if (File::exists($path)) {
            File::deleteDirectories($path);
        }


        // Fake the 'storage' disk
        Storage::fake('storage');

        // Update the root path to point to the testing folder
        config(['filesystems.disks.storage.root' => storage_path('framework/testing/disks/storage')]);

        Cache::flush();
        if(!$setUpDataset) {
            return;
        }
        $this->importDataset($visibility);
    }
    // Method to insert dataset into the database and move files
    public function importDataset($visibility)
    {
        // Step 1: Manually prepare the data
        $mappedData = [
            'images' => [
                [
                    'filename' => '20230926_140614_jpg.rf.7b79446b4e72d2e86bd983081a65a335_da_6809511491e90.jpg',
                    'width' => 3468,
                    'height' => 4624,
                    'size' => 1536388,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.3719723183391, 'y' => 0.51254325259516, 'width' => 0.31141868512110726, 'height' => 0.12975778546712802],
                        ['class_id' => '1', 'x' => 0.031718569780854, 'y' => 0.29195501730104, 'width' => 0.9682814302191465, 'height' => 0.14057093425605535],
                    ]
                ],
                [
                    'filename' => '20230929_143529_jpg.rf.0c313cba0cd8d0eae4a791fe5ce2c557_da_68095114922ee.jpg',
                    'width' => 4000,
                    'height' => 3000,
                    'size' => 1704541,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.0025, 'y' => 0.096666666666667, 'width' => 0.75, 'height' => 0.9033333333333333],
                        ['class_id' => '1', 'x' => 0.415, 'y' => 0.051666666666667, 'width' => 0.57, 'height' => 0.13833333333333334],
                        ['class_id' => '2', 'x' => 0.52375, 'y' => 0.25, 'width' => 0.3725, 'height' => 0.3566666666666667],
                    ]
                ],
                [
                    'filename' => 'image00031_jpeg.rf.3c63e68e069d0d9cedad667c5dfddfbc_da_6809511492980.jpg',
                    'width' => 4284,
                    'height' => 5712,
                    'size' => 3877293,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.16106442577031, 'y' => 0.51295518207283, 'width' => 0.7002801120448179, 'height' => 0.34663865546218486],
                    ]
                ]
            ],
            'classes' => [
                ['name' => 'me'],
                ['name' => 'skaly'],
                ['name' => 'voda']
            ]
        ];

        $requestData = [
            'display_name' => 'test_dataset',
            'unique_name' => 'valid_bbox',
            'format' => 'YOLO',
            'metadata' => \App\Models\MetadataValue::pluck('id')->take(3)->toArray(),
            'technique' => 'Bounding box',
            'categories' => \App\Models\Category::pluck('id')->take(2)->toArray(),
            'description' => 'Test dataset with folder structure'
        ];

        $user = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($user);
        // Step 2: Insert into database
        $dataset = \App\Models\Dataset::create([
            'user_id' => $user->id,
            'display_name' => $requestData['display_name'],
            'unique_name' => $requestData['unique_name'],
            'description' => $requestData['description'],
            'num_images' => count($mappedData['images']),
            'total_size' => array_sum(array_column($mappedData['images'], 'size')),
            'annotation_technique' => $requestData['technique'],
            'is_public' => $visibility === 'public' ? 1 : 0,
            'is_approved' => $visibility === 'public' ? 1 : 0,
        ]);

        // Step 3: Save Classes
        $classIds = [];
        foreach ($mappedData['classes'] as $class) {
            $classIds[] = \App\Models\AnnotationClass::create([
                'dataset_id' => $dataset->id,
                'name' => $class['name'],
            ])->id;
        }

        // Step 4: Save Images and Annotations
        foreach ($mappedData['images'] as $img) {
            $image = \App\Models\Image::create([
                'dataset_id' => $dataset->id,
                'dataset_folder' => $dataset->unique_name,
                'filename' => $img['filename'],
                'width' => $img['width'],
                'height' => $img['height'],
                'size' => $img['size'],
            ]);

            // Save Annotations
            foreach ($img['annotations'] as $annotation) {
                \App\Models\AnnotationData::create([
                    'image_id' => $image->id,
                    'annotation_class_id' => $classIds[$annotation['class_id']],
                    'x' => $annotation['x'],
                    'y' => $annotation['y'],
                    'width' => $annotation['width'],
                    'height' => $annotation['height'],
                    'segmentation' => $annotation['segmentation'] ?? null,
                    'svg_path' => \App\Utils\Util::generateSvgPath($annotation, $img['width'], $img['height']),
                ]);
            }
        }

        // Step 5: Save dataset metadata
        foreach ($requestData['metadata'] as $value) {
            \App\Models\DatasetMetadata::create([
                'dataset_id' => $dataset->id,
                'metadata_value_id' => $value,
            ]);
        }

        // Step 6: Save dataset categories
        foreach ($requestData['categories'] as $id) {
            \App\Models\DatasetCategory::create([
                'dataset_id' => $dataset->id,
                'category_id' => $id,
            ]);
        }

        // Step 7: Move Files to Storage Directory
        $sourceDir = app_path('../tests/Data/' . $requestData['unique_name']);
        $destinationDir = storage_path("framework/testing/disks/storage/app/{$visibility}/datasets/" . $requestData['unique_name']);
        File::copyDirectory($sourceDir, $destinationDir);
        /*$this->assertDirectoryExists(storage_path('framework/testing/disks/storage/app/private/datasets/' . $requestData['unique_name']));
        $this->assertDatabaseHas('datasets', [
            'unique_name' => $requestData['unique_name'],
        ]);*/

    }


    public function importDataset2($visibility)
    {
        // Step 1: Manually prepare the data
        $mappedData = [
            'images' => [
                [
                    'filename' => '220230926_140614_jpg.rf.7b79446b4e72d2e86bd983081a65a335_da_6809511491e90.jpg',
                    'width' => 3468,
                    'height' => 4624,
                    'size' => 1536388,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.3719723183391, 'y' => 0.51254325259516, 'width' => 0.31141868512110726, 'height' => 0.12975778546712802],
                        ['class_id' => '1', 'x' => 0.031718569780854, 'y' => 0.29195501730104, 'width' => 0.9682814302191465, 'height' => 0.14057093425605535],
                    ]
                ],
                [
                    'filename' => '220230929_143529_jpg.rf.0c313cba0cd8d0eae4a791fe5ce2c557_da_68095114922ee.jpg',
                    'width' => 4000,
                    'height' => 3000,
                    'size' => 1704541,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.0025, 'y' => 0.096666666666667, 'width' => 0.75, 'height' => 0.9033333333333333],
                        ['class_id' => '1', 'x' => 0.415, 'y' => 0.051666666666667, 'width' => 0.57, 'height' => 0.13833333333333334],
                        ['class_id' => '2', 'x' => 0.52375, 'y' => 0.25, 'width' => 0.3725, 'height' => 0.3566666666666667],
                    ]
                ],
                [
                    'filename' => '2image00031_jpeg.rf.3c63e68e069d0d9cedad667c5dfddfbc_da_6809511492980.jpg',
                    'width' => 4284,
                    'height' => 5712,
                    'size' => 3877293,
                    'annotations' => [
                        ['class_id' => '0', 'x' => 0.16106442577031, 'y' => 0.51295518207283, 'width' => 0.7002801120448179, 'height' => 0.34663865546218486],
                    ]
                ]
            ],
            'classes' => [
                ['name' => 'me'],
                ['name' => 'skaly'],
                ['name' => 'voda']
            ]
        ];

        $requestData = [
            'display_name' => 'test_dataset2',
            'unique_name' => 'valid_bbox2',
            'format' => 'YOLO',
            'metadata' => \App\Models\MetadataValue::pluck('id')->skip(3)->take(3)->toArray(),
            'technique' => 'Bounding box',
            'categories' => \App\Models\Category::pluck('id')->skip(2)->take(2)->toArray(),
            'description' => 'Test dataset with folder structure'
        ];

        $user = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($user);
        // Step 2: Insert into database
        $dataset = \App\Models\Dataset::create([
            'user_id' => $user->id,
            'display_name' => $requestData['display_name'],
            'unique_name' => $requestData['unique_name'],
            'description' => $requestData['description'],
            'num_images' => count($mappedData['images']),
            'total_size' => array_sum(array_column($mappedData['images'], 'size')),
            'annotation_technique' => $requestData['technique'],
            'is_public' => $visibility === 'public' ? 1 : 0,
            'is_approved' => $visibility === 'public' ? 1 : 0,
        ]);

        // Step 3: Save Classes
        $classIds = [];
        foreach ($mappedData['classes'] as $class) {
            $classIds[] = \App\Models\AnnotationClass::create([
                'dataset_id' => $dataset->id,
                'name' => $class['name'],
            ])->id;
        }

        // Step 4: Save Images and Annotations
        foreach ($mappedData['images'] as $img) {
            $image = \App\Models\Image::create([
                'dataset_id' => $dataset->id,
                'dataset_folder' => $dataset->unique_name,
                'filename' => $img['filename'],
                'width' => $img['width'],
                'height' => $img['height'],
                'size' => $img['size'],
            ]);

            // Save Annotations
            foreach ($img['annotations'] as $annotation) {
                \App\Models\AnnotationData::create([
                    'image_id' => $image->id,
                    'annotation_class_id' => $classIds[$annotation['class_id']],
                    'x' => $annotation['x'],
                    'y' => $annotation['y'],
                    'width' => $annotation['width'],
                    'height' => $annotation['height'],
                    'segmentation' => $annotation['segmentation'] ?? null,
                    'svg_path' => \App\Utils\Util::generateSvgPath($annotation, $img['width'], $img['height']),
                ]);
            }
        }

        // Step 5: Save dataset metadata
        foreach ($requestData['metadata'] as $value) {
            \App\Models\DatasetMetadata::create([
                'dataset_id' => $dataset->id,
                'metadata_value_id' => $value,
            ]);
        }

        // Step 6: Save dataset categories
        foreach ($requestData['categories'] as $id) {
            \App\Models\DatasetCategory::create([
                'dataset_id' => $dataset->id,
                'category_id' => $id,
            ]);
        }

        // Step 7: Move Files to Storage Directory
        $sourceDir = app_path('../tests/Data/' . $requestData['unique_name']);
        $destinationDir = storage_path("framework/testing/disks/storage/app/{$visibility}/datasets/" . $requestData['unique_name']);
        File::copyDirectory($sourceDir, $destinationDir);
        /*$this->assertDirectoryExists(storage_path("framework/testing/disks/storage/app/{$visibility}/datasets/" . $requestData['unique_name']));
        $this->assertDatabaseHas('datasets', [
            'unique_name' => $requestData['unique_name'],
        ]);*/

    }

}
