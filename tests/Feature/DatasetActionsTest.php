<?php

namespace Tests\Feature;

use App\Livewire\Forms\InviteUser;
use App\Livewire\FullPages\AdminDatasets;
use App\Models\Dataset;
use App\Models\DatasetStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DatasetActionsTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage();
    }

    public function test_update_statistics(): void
    {
        $dataset = Dataset::first();
        $dataset->update([
            'is_approved' => 1,
        ]);
        $imageCount = $dataset->images()->count();
        $annotationCount = $dataset->annotations()->count();
        $classesCount = $dataset->classes()->count();

        Livewire::test(AdminDatasets::class)
            ->call('recalculateStats')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Statistics recalculated successfully');

        $totalImageCount = DatasetStatistics::all()->sum('image_count');
        $totalAnnotationCount = DatasetStatistics::all()->sum('annotation_count');
        $totalClassesCount = DatasetStatistics::all()->sum('class_count');
        $this->assertEquals($imageCount, $totalImageCount);
        $this->assertEquals($annotationCount, $totalAnnotationCount);
        $this->assertEquals($classesCount, $totalClassesCount);
    }
    public function test_moving_dataset_between_folders(): void
    {
        // Arrange: Get the first dataset and ensure it is public
        $dataset = Dataset::first();

        $datasetUniqueName = $dataset->unique_name;
        $publicPath = 'app/public/datasets/' . $datasetUniqueName;
        $privatePath = 'app/private/datasets/' . $datasetUniqueName;

        // Assert: Check that the dataset exists in the public folder initially
        $this->assertTrue(Storage::exists($publicPath));
        $this->assertFalse(Storage::exists($privatePath));

        // Act: Simulate the toggleVisibility method in the Livewire component
        Livewire::test(AdminDatasets::class)
            ->call('toggleVisibility', $dataset->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Dataset visibility changed to private');

        // Assert: After moving to the private folder, dataset should exist in the private folder and not in the public folder
        $this->assertFalse(Storage::exists($publicPath));
        $this->assertTrue(Storage::exists($privatePath));
        $dataset->refresh();
        $this->assertFalse($dataset->is_public);

        // Act: Call the method to move the dataset back to the public folder
        Livewire::test(AdminDatasets::class)
            ->call('toggleVisibility', $dataset->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Dataset visibility changed to public');

        // Assert: After moving back to the public folder, dataset should exist in the public folder and not in the private folder
        $this->assertTrue(Storage::exists($publicPath));
        $this->assertFalse(Storage::exists($privatePath));

        // Assert: Ensure that the dataset is now public again
        $dataset->refresh();
        $this->assertTrue($dataset->is_public);
    }

    public function test_change_owner(): void
    {
        // Arrange: Get the first dataset and the new owner (we can create or get another user for the new owner)
        $dataset = Dataset::first();
        $newOwner = $this->getUser('user');

        // Act: Call the changeOwner method in the Livewire component
        Livewire::test(AdminDatasets::class)
            ->call('changeOwner', $dataset->id, $newOwner->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Owner changed successfully!'
            );

        // Assert: Check if the dataset's user_id has been updated to the new owner's id
        $dataset->refresh(); // Refresh to get the latest dataset data
        $this->assertEquals($newOwner->id, $dataset->user_id);
    }

    public function test_change_owner_dataset_not_found(): void
    {
        $newOwner = $this->getUser('user');

        Livewire::test(AdminDatasets::class)
            ->call('changeOwner', 9999, $newOwner->id)
            ->assertDispatched('flash-msg',
                type: 'error',
                message: 'An error occurred'
            );
    }

    public function test_change_owner_user_not_found(): void
    {
        $dataset = Dataset::first();

        Livewire::test(AdminDatasets::class)
            ->call('changeOwner', $dataset->id, 9999)
            ->assertDispatched('flash-msg',
                type: 'error',
                message: 'An error occurred'
            );
    }

}
