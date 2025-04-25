<?php

namespace Tests\Feature;

use App\Livewire\FullPages\AdminDashboard;
use App\Livewire\FullPages\AdminDatasets;
use App\Models\Category;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }

    public function test_category_create()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);

        Livewire::test(AdminDashboard::class)
            ->call('saveCategory', 'Test Category')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Category saved successfully');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }
    public function test_category_update()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $category = Category::first();

        Livewire::test(AdminDashboard::class)
            ->call('saveCategory', 'Updated category name', $category->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Category saved successfully');

        $this->assertDatabaseHas('categories', [
            'name' => 'Updated category name',
            'id' => $category->id,
        ]);
    }
    public function test_category_delete()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $category = Category::first();

        Livewire::test(AdminDashboard::class)
            ->call('deleteCategory', $category->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Category deleted successfully');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
    public function test_metadataType_create()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);

        Livewire::test(AdminDashboard::class)
            ->call('saveType', 'Created metadata type name')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Type saved successfully');

        $this->assertDatabaseHas('metadata_types', [
            'name' => 'Created metadata type name',
        ]);
    }
    public function test_metadataType_update()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $type = MetadataType::first();

        Livewire::test(AdminDashboard::class)
            ->call('saveType', 'Updated type name', $type->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Type saved successfully');

        $this->assertDatabaseHas('metadata_types', [
            'id' => $type->id,
            'name' => 'Updated type name',
        ]);
    }

    public function test_metadataType_delete()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $type = MetadataType::first();;

        Livewire::test(AdminDashboard::class)
            ->call('deleteType', $type->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Type deleted successfully');

        $this->assertDatabaseMissing('metadata_types', ['id' => $type->id]);
    }

    public function test_metadataValue_create()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $type = MetadataType::first();

        Livewire::test(AdminDashboard::class)
            ->call('saveValue', $type->id, 'New value')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Value saved successfully');

        $this->assertDatabaseHas('metadata_values', [
            'metadata_type_id' => $type->id,
            'value' => 'New value',
        ]);
    }

    public function test_metadataValue_update()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $value = MetadataValue::first();

        Livewire::test(AdminDashboard::class)
            ->call('saveValue', $value->metadata_type_id, 'Updated value', $value->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Value saved successfully');

        $this->assertDatabaseHas('metadata_values', [
            'id' => $value->id,
            'value' => 'Updated value',
        ]);
    }

    public function test_metadataValue_delete()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);
        $value = MetadataValue::first();

        Livewire::test(AdminDashboard::class)
            ->call('deleteValue', $value->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Value deleted successfully');

        $this->assertDatabaseMissing('metadata_values', ['id' => $value->id]);
    }
}
