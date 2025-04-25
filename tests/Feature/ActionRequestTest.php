<?php

namespace Tests\Feature;

use App\ActionRequestService\ActionRequestService;
use App\Models\ActionRequest;
use App\Models\Dataset;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }


    /*
     *  APPROVE TESTS
     * */
    public function test_new_handler_approve(): void
    {
        $this->importDataset('private');

        // Get the user and admin dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $admin = \App\Models\User::where('role', 'admin')->first();

        // Authenticate as the user
        $this->actingAs($user);

        // Create a new action request for 'new'
        $actionRequest = new ActionRequestService();
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first(); // Fetch the dataset by unique name
        $actionRequest->createRequest('new', [
            'dataset_id' => $dataset->id,  // Use dynamic dataset ID
            'dataset_unique_name' => 'valid_bbox',
        ]);

        // Assert that the action request was created successfully
        $this->assertDatabaseHas('action_requests', [
            'type' => 'new',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        // Authenticate as admin
        $this->actingAs($admin);

        // Fetch the action request
        $request = ActionRequest::first();

        // Resolve the action request as approved
        $actionRequest->resolveRequest($request, 'approve', 'Auto-approved by system');

        // Assert that the dataset has been updated
        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id, // Use dynamic dataset ID
            'is_approved' => true,
            'is_public' => true,
        ]);

        // Assert that the action request was approved
        $this->assertDatabaseHas('action_requests', [
            'type' => 'new',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'approved',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);

        // Assert that the dataset directory exists
        $this->assertDirectoryExists(Storage::path('app/public/datasets/' . $dataset->unique_name));
    }

    public function test_edit_handler_approve(): void
    {
        $this->importDataset('public');

        // Get the user and admin dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $admin = \App\Models\User::where('role', 'admin')->first();

        // Authenticate as the user
        $this->actingAs($user);

        // Fetch the dataset dynamically by unique name
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Fetch the categories and metadata dynamically based on the dataset's relationships
        $categories = $dataset->categories; // Assuming Dataset model has a 'categories' relationship
        $metadata = $dataset->metadataValues; // Assuming Dataset model has a 'metadataValues' relationship

        // Create the action request for 'edit'
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('edit', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic unique name
            'display_name' => 'random_name',
            'description' => 'new_description',
            'categories' => $categories->pluck('id')->toArray(), // Use dynamic category IDs
            'metadata' => $metadata->pluck('id')->toArray(), // Use dynamic metadata IDs
        ]);

        // Authenticate as the admin
        $this->actingAs($admin);

        // Fetch the first action request
        $request = ActionRequest::first();

        // Resolve the action request as approved
        $actionRequest->resolveRequest($request, 'approve');

        // Fetch the updated dataset dynamically
        $dataset = \App\Models\Dataset::find($dataset->id); // Fetch the dataset by ID

        // Assert that the dataset has been updated
        $this->assertEquals('random_name', $dataset->display_name);
        $this->assertEquals('new_description', $dataset->description);
        $this->assertEquals($categories->pluck('id')->toArray(), $dataset->categories->pluck('id')->toArray());
        $this->assertEquals($metadata->pluck('id')->toArray(), $dataset->metadataValues->pluck('id')->toArray());

        // Assert that the action request was approved
        $this->assertDatabaseHas('action_requests', [
            'type' => 'edit',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'approved',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_extend_handler_approve(): void
    {
        // Import the parent and child datasets
        $this->importDataset('public');
        $this->importDataset2('private');

        // Get the dataset IDs dynamically based on unique names
        $parentDataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();
        $childDataset = \App\Models\Dataset::where('unique_name', 'valid_bbox2')->first();

        // Initial dataset count (before extend)
        $initialDatasetCount = \Illuminate\Support\Facades\DB::table('datasets')->count();

        // Initial image count for the parent dataset
        $initialImageCount = \Illuminate\Support\Facades\DB::table('images')->where('dataset_id', $parentDataset->id)->count();

        // Initial image count for the child dataset
        $initialChildImageCount = \Illuminate\Support\Facades\DB::table('images')->where('dataset_id', $childDataset->id)->count();

        // Set up the test as a user
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Create the extend request
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('extend', [
            'dataset_id' => $parentDataset->id, // Use dynamic parent dataset ID
            'dataset_unique_name' => $parentDataset->unique_name, // Use dynamic parent dataset unique name
            'child_unique_name' => $childDataset->unique_name, // Use dynamic child dataset unique name
        ]);

        // Set up as admin for approval
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Resolve the request
        $request = ActionRequest::first();
        $actionRequest->resolveRequest($request, 'approve');

        // Assert that the child dataset was deleted
        $this->assertDatabaseMissing('datasets', [
            'id' => $childDataset->id, // Use dynamic child dataset ID
        ]);

        // Assert that the parent dataset is still in the DB and is updated
        $this->assertDatabaseHas('datasets', [
            'id' => $parentDataset->id, // Use dynamic parent dataset ID
        ]);

        // Assert that the number of images in the parent dataset has increased
        $finalImageCount = \Illuminate\Support\Facades\DB::table('images')->where('dataset_id', $parentDataset->id)->count();
        $this->assertGreaterThan($initialImageCount, $finalImageCount, 'Images from the child dataset were not added to the parent dataset.');

        // Assert that the images from the child dataset no longer exist in DB
        $finalChildImageCount = \Illuminate\Support\Facades\DB::table('images')->where('dataset_id', $childDataset->id)->count();
        $this->assertEquals(0, $finalChildImageCount, 'Images from the child dataset were not deleted.');

        // Assert that the files from the child dataset are now in the parent dataset’s storage
        $this->assertDirectoryExists(Storage::path('app/public/datasets/' . $parentDataset->unique_name));
        $this->assertDirectoryDoesNotExist(Storage::path('app/public/datasets/' . $childDataset->unique_name));
    }

    public function test_reduce_handler_approve(): void
    {
        // Import the dataset
        $this->importDataset('public');

        // Get the dataset and the image to delete dynamically
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();
        $imageToDelete = \App\Models\Image::where('dataset_id', $dataset->id)->first();

        // Ensure there's an image to delete
        $this->assertNotNull($imageToDelete, "No images found for dataset 'valid_bbox'");

        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Create the reduce request
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('reduce', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic dataset unique name
            'image_ids' => [$imageToDelete->id], // Use dynamic image ID
        ]);

        // Set up as admin for approval
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Resolve the request
        $request = ActionRequest::first();
        $actionRequest->resolveRequest($request, 'approve');

        // Assert that the image is deleted from the database
        $this->assertDatabaseMissing('images', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'id' => $imageToDelete->id, // Use dynamic image ID
        ]);

        // Assert that the image file is deleted from storage
        Storage::assertMissing('app/public/datasets/' . $dataset->unique_name . '/' . $imageToDelete->filename);

        // Assert that the action request status is updated to approved
        $this->assertDatabaseHas('action_requests', [
            'type' => 'reduce',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'approved',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_delete_handler_approve(): void
    {
        // Import the dataset
        $this->importDataset('public');

        // Get the dataset dynamically
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Set up the test as a user
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Create the delete request dynamically using the dataset's ID
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('delete', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic dataset unique name
        ]);

        // Set up as admin for approval
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Resolve the request
        $request = ActionRequest::first();
        $actionRequest->resolveRequest($request, 'approve');

        // Assert that the dataset's folder is deleted from storage
        Storage::assertMissing('app/public/datasets/' . $dataset->unique_name);

        // Assert that the action request has been approved
        $this->assertDatabaseHas('action_requests', [
            'type' => 'delete',
            'dataset_id' => null, // Since dataset is deleted, it should be null
            'status' => 'approved',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);
    }



    /*
     *  REJECT TESTS
     * */
    public function test_new_handler_reject(): void
    {
        // Import the dataset
        $this->importDataset('private');

        // Get the dataset dynamically
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Set up the test as a user
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Create the 'new' request dynamically using the dataset's ID
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('new', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic dataset unique name
        ]);

        // Set up as admin for rejection
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Resolve the request
        $request = ActionRequest::first();
        $actionRequest->resolveRequest($request, 'reject', 'Auto-rejected by system');

        // Assert that the dataset was not added (missing in the database)
        $this->assertDatabaseMissing('datasets', [
            'id' => $dataset->id, // Assert that the dataset ID is missing after rejection
        ]);

        // Assert that the action request has been rejected
        $this->assertDatabaseHas('action_requests', [
            'type' => 'new',
            'dataset_id' => null, // Since the dataset was rejected, it should be null
            'status' => 'rejected',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);

        // Assert that the dataset folder was not created in storage
        $this->assertDirectoryDoesNotExist(Storage::path('app/public/datasets/' . $dataset->unique_name));
    }

    public function test_edit_handler_reject(): void
    {
        // Import the dataset
        $this->importDataset('public');

        // Get the user dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Get the dataset dynamically
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Get the categories and metadata dynamically based on the dataset
        $categories = $dataset->categories->pluck('id')->toArray();
        $metadata = $dataset->metadataValues->pluck('id')->toArray();

        // Create the action request to edit the dataset
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('edit', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic dataset unique name
            'display_name' => 'random_name',
            'description' => 'new_description',
            'categories' => $categories, // Use dynamic categories
            'metadata' => $metadata, // Use dynamic metadata
        ]);

        // Fetch the dataset before rejection dynamically
        $datasetBefore = \App\Models\Dataset::find($dataset->id);
        $categoriesBefore = $datasetBefore->categories->pluck('id')->toArray();
        $metadataBefore = $datasetBefore->metadataValues->pluck('id')->toArray();
        $nameBefore = $datasetBefore->display_name;
        $descriptionBefore = $datasetBefore->description;

        // Act as admin to resolve the request
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $request = ActionRequest::first();
        $actionRequest->resolveRequest($request, 'reject', 'Auto-rejected by system');

        // Fetch the dataset after rejection dynamically
        $datasetAfter = \App\Models\Dataset::find($dataset->id);
        $categoriesAfter = $datasetAfter->categories->pluck('id')->toArray();
        $metadataAfter = $datasetAfter->metadataValues->pluck('id')->toArray();
        $nameAfter = $datasetAfter->display_name;
        $descriptionAfter = $datasetAfter->description;

        // Assert that the dataset's name, description, categories, and metadata are unchanged
        $this->assertEquals($nameBefore, $nameAfter);
        $this->assertEquals($descriptionBefore, $descriptionAfter);
        $this->assertEquals($categoriesBefore, $categoriesAfter);
        $this->assertEquals($metadataBefore, $metadataAfter);

        // Ensure the action request status is 'rejected'
        $this->assertDatabaseHas('action_requests', [
            'type' => 'edit',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'rejected',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_extend_handler_reject(): void
    {
        // Import the datasets
        $this->importDataset('public');
        $this->importDataset2('private');

        // Get the user dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Get the datasets dynamically based on unique names
        $mainDataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();
        $childDataset = \App\Models\Dataset::where('unique_name', 'valid_bbox2')->first();

        // Create the action request to extend (merge datasets)
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('extend', [
            'dataset_id' => $mainDataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $mainDataset->unique_name,
            'child_unique_name' => $childDataset->unique_name, // Use dynamic child unique name
        ]);

        // Get the admin user to resolve the request
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $request = ActionRequest::first();

        // Before rejecting, get the image count of both datasets dynamically
        $initialMainImageCount = $mainDataset->images->count();
        $initialChildImageCount = $childDataset->images->count();

        // Reject the extend request
        $actionRequest->resolveRequest($request, 'reject', 'Auto-rejected by system');

        // After rejection, the child dataset should be deleted
        $this->assertDatabaseMissing('datasets', [
            'id' => $childDataset->id, // Use dynamic child dataset ID
        ]);

        // Ensure the child dataset folder is removed from storage
        $this->assertDirectoryDoesNotExist(Storage::path('app/public/datasets/' . $childDataset->unique_name));

        // The main dataset should not have been altered, so the image count should stay the same
        $mainDatasetAfter = \App\Models\Dataset::find($mainDataset->id);
        $this->assertEquals($initialMainImageCount, $mainDatasetAfter->images->count());

        // The action request should be rejected and recorded
        $this->assertDatabaseHas('action_requests', [
            'type' => 'extend',
            'dataset_id' => $mainDataset->id, // Use dynamic dataset ID
            'status' => 'rejected',
            'user_id' => $user->id,
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_delete_handler_reject(): void
    {
        // Import the dataset to test the delete action
        $this->importDataset('public');

        // Get the user and create the delete request dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Fetch the dataset dynamically using unique name
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Create the delete request using dynamic dataset ID
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('delete', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic unique name
        ]);

        // Get the admin user and process the action request
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $request = ActionRequest::first();

        // Reject the delete request
        $actionRequest->resolveRequest($request, 'reject', 'Auto-rejected by system');

        // Ensure the dataset is still in the database (not deleted)
        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id, // Use dynamic dataset ID
        ]);

        // Ensure the action request has been marked as rejected
        $this->assertDatabaseHas('action_requests', [
            'type' => 'delete',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'rejected',
        ]);

        // Ensure the dataset directory still exists in storage
        $this->assertDirectoryExists(Storage::path('app/public/datasets/' . $dataset->unique_name)); // Use dynamic unique name
    }

    public function test_reduce_handler_reject(): void
    {
        // Import the dataset to test the reduce action
        $this->importDataset('public');

        // Get the user and create the reduce request dynamically
        $user = \App\Models\User::where('role', 'user')->first();
        $this->actingAs($user);

        // Fetch the dataset dynamically using unique name
        $dataset = \App\Models\Dataset::where('unique_name', 'valid_bbox')->first();

        // Fetch image IDs dynamically
        $imageIds = \App\Models\Image::where('dataset_id', $dataset->id)->take(2)->pluck('id')->toArray();

        // Create the reduce request using dynamic dataset ID and image IDs
        $actionRequest = new ActionRequestService();
        $actionRequest->createRequest('reduce', [
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'dataset_unique_name' => $dataset->unique_name, // Use dynamic unique name
            'image_ids' => $imageIds, // Use dynamic image IDs
        ]);

        // Get the admin user and process the action request
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $request = ActionRequest::first();

        // Reject the reduce request
        $actionRequest->resolveRequest($request, 'reject', 'Auto-rejected by system');

        // Ensure the dataset is not modified (not deleted)
        $this->assertDatabaseHas('datasets', [
            'id' => $dataset->id, // Use dynamic dataset ID
        ]);

        // Ensure the images are not deleted
        foreach ($imageIds as $imageId) {
            $this->assertDatabaseHas('images', [
                'id' => $imageId, // Ensure dynamic image ID exists
                'dataset_id' => $dataset->id, // Ensure image belongs to the correct dataset
            ]);
        }

        // Ensure the action request status is 'rejected'
        $this->assertDatabaseHas('action_requests', [
            'type' => 'reduce',
            'dataset_id' => $dataset->id, // Use dynamic dataset ID
            'status' => 'rejected',
        ]);
    }

}
