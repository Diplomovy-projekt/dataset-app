<?php

namespace Tests\Feature;

use App\Configs\AppConfig;
use App\Livewire\FullPages\DatasetBuilder;
use App\Models\Category;
use App\Models\Dataset;
use App\Models\DatasetCategory;
use App\Models\DatasetMetadata;
use App\Models\MetadataType;
use App\Models\MetadataValue;
use App\Utils\QueryUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatasetBuilderTest extends TestCase
{
    protected $datasetBuilder;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
        $this->importDataset('public');
        $this->importDataset2('public');
        $this->datasetBuilder = new DatasetBuilder();
    }

    public function test_annotation_technique_stage()
    {
        // Given: The user is on the first stage (annotation technique)
        $admin = $this->getUser();
        $this->actingAs($admin);

        // When: Selecting a technique and advancing to the next stage
        Livewire::test(DatasetBuilder::class)
            ->set('selectedAnnotationTechnique', AppConfig::ANNOTATION_TECHNIQUES['POLYGON'])
            ->call('nextStage')
            ->assertSet('currentStage', 1)
            ->assertSet('polygonDatasetsStats', function ($stats) {
                return isset($stats['numDatasets']);
            })
            ->call('nextStage')
            ->assertSet('currentStage', 2);
    }

    public function test_categories_stage_filters_and_maps_categories_for_polygon()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);

        // Create categories and datasets
        $categoryWithPolygon = Category::factory()->create(['name' => 'PolygonCategory']);
        $categoryWithoutPolygon = Category::factory()->create(['name' => 'NonPolygonCategory']);

        $polygonDataset = Dataset::factory()->create([
            'annotation_technique' => 'Polygon',
            'unique_name' => 'polygon-dataset',
            'is_approved' => true,
            'is_public' => true,
        ]);
        $polygonDataset->categories()->attach($categoryWithPolygon);

        $nonPolygonDataset = Dataset::factory()->create([
            'annotation_technique' => 'Classification',
            'unique_name' => 'non-polygon-dataset',
            'is_approved' => true,
            'is_public' => true,
        ]);
        $nonPolygonDataset->categories()->attach($categoryWithoutPolygon);

        Livewire::test(DatasetBuilder::class)
            ->set('selectedAnnotationTechnique', AppConfig::ANNOTATION_TECHNIQUES['POLYGON'])
            ->call('nextStage')
            ->call('nextStage')
            ->assertSet('categories', function ($categories) use ($categoryWithPolygon, $categoryWithoutPolygon) {
                $ids = collect($categories)->pluck('id');
                return $ids->contains($categoryWithPolygon->id)
                    && !$ids->contains($categoryWithoutPolygon->id);
            });
    }

    public function test_categories_stage_does_not_filter_categories_for_bounding_box()
    {
        $admin = $this->getUser();
        $this->actingAs($admin);

        // Create categories and datasets
        $categoryWithBoundingBox = Category::factory()->create(['name' => 'BoundingBoxCategory']);
        $categoryWithPolygon = Category::factory()->create(['name' => 'PolygonCategory']);

        $boundingBoxDataset = Dataset::factory()->create([
            'annotation_technique' => 'BoundingBox',
            'unique_name' => 'boundingbox-dataset',
            'is_approved' => true,
            'is_public' => true,
        ]);
        $boundingBoxDataset->categories()->attach($categoryWithBoundingBox);

        $polygonDataset = Dataset::factory()->create([
            'annotation_technique' => 'Polygon',
            'unique_name' => 'polygon-dataset',
            'is_approved' => true,
            'is_public' => true,
        ]);
        $polygonDataset->categories()->attach($categoryWithPolygon);

        Livewire::test(DatasetBuilder::class)
            ->set('selectedAnnotationTechnique', AppConfig::ANNOTATION_TECHNIQUES['BOUNDING_BOX'])
            ->call('nextStage')
            ->call('nextStage')
            ->assertSet('categories', function ($categories) use ($categoryWithBoundingBox, $categoryWithPolygon) {
                $ids = collect($categories)->pluck('id');
                return $ids->contains($categoryWithBoundingBox->id)
                    && $ids->contains($categoryWithPolygon->id);
            });
    }

    public function test_datasets_stage_logic()
    {
        // Create categories
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);


        // Create metadata values and types
        $metadataType1 = MetadataType::factory()->create(['description' => 'Desc 1']);
        $metadataType2 = MetadataType::factory()->create(['description' => 'Desc 2']);
        $metadataType3 = MetadataType::factory()->create(['description' => 'Desc 3']);


        $metadataValue1 = MetadataValue::factory()->create(['metadata_type_id' => $metadataType1->id]);
        $metadataValue2 = MetadataValue::factory()->create(['metadata_type_id' => $metadataType2->id]);
        $metadataValue3 = MetadataValue::factory()->create(['metadata_type_id' => $metadataType3->id]);

        // Create datasets
        $dataset1 = Dataset::factory()->create(['is_public' => true, 'is_approved' => true, 'unique_name' => 'ds1']);
        $dataset2 = Dataset::factory()->create(['is_public' => true, 'is_approved' => true, 'unique_name' => 'ds2']);
        $dataset3 = Dataset::factory()->create(['is_public' => true, 'is_approved' => true, 'unique_name' => 'ds3']);
        $dataset4 = Dataset::factory()->create(['is_public' => true, 'is_approved' => true, 'unique_name' => 'ds4']);
        $dataset5 = Dataset::factory()->create(['is_public' => true, 'is_approved' => true, 'unique_name' => 'ds5']);


        // Assign categories
        $dataset1->categories()->attach($category1);
        $dataset2->categories()->attach($category1);
        $dataset3->categories()->attach($category2);
        //$dataset4->categories()->attach($category1);
        $dataset5->categories()->attach($category2);

        // Assign metadata
        DatasetMetadata::factory()->create(['dataset_id' => $dataset1->id, 'metadata_value_id' => $metadataValue1->id]);
        DatasetMetadata::factory()->create(['dataset_id' => $dataset2->id, 'metadata_value_id' => $metadataValue2->id]);

        // Setup the builder object
        $builder = new DatasetBuilder();
        $builder->selectedMetadataValues = [
            $metadataValue1->id => true,
            $metadataValue2->id => false,
        ];
        $builder->skipTypes = [$metadataType3->id];
        $builder->selectedCategories = [$category1->id, $category2->id];

        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('datasetsStage');
        $method->setAccessible(true);
        $method->invoke($builder);


        // Assert datasets matched (with or without metadata)
        $expectedIds = [
            $dataset1->id,
            $dataset3->id,
            $dataset5->id,
        ];

        $this->assertEqualsCanonicalizing($expectedIds, $builder->datasetIds);
    }


}
