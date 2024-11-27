<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\PropertyType;
use App\Models\PropertyValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch PropertyTypes by name
        $languageType = PropertyType::where('name', 'Language')->first();
        $centuryType = PropertyType::where('name', 'Century')->first();
        $categoryType = PropertyType::where('name', 'Category')->first();

        // Check if the property types exist before proceeding
        if (!$languageType || !$centuryType || !$categoryType) {
            throw new \Exception('One or more PropertyTypes not found.');
        }

        // Seed PropertyValues for each PropertyType (Same as your existing seeder)
        $values = [
            // Languages
            [
                'property_type_id' => $languageType->id,
                'value' => 'English',
            ],
            [
                'property_type_id' => $languageType->id,
                'value' => 'French',
            ],
            [
                'property_type_id' => $languageType->id,
                'value' => 'Spanish',
            ],
            // Centuries
            [
                'property_type_id' => $centuryType->id,
                'value' => '19th Century',
            ],
            [
                'property_type_id' => $centuryType->id,
                'value' => '20th Century',
            ],
            [
                'property_type_id' => $centuryType->id,
                'value' => '21st Century',
            ],
            // Categories
            [
                'property_type_id' => $categoryType->id,
                'value' => 'Digits',
            ],
            [
                'property_type_id' => $categoryType->id,
                'value' => 'Glyphs',
            ],
            [
                'property_type_id' => $categoryType->id,
                'value' => 'Letters',
            ],
            [
                'property_type_id' => $categoryType->id,
                'value' => 'Words',
            ],
            [
                'property_type_id' => $categoryType->id,
                'value' => 'Ciphers',
            ],
        ];

        // Insert the PropertyValues into the PropertyValues table
        foreach ($values as $value) {
            PropertyValue::firstOrCreate($value);
        }

        // Now associate PropertyValues with DatasetProperty
        // Fetch all datasets (assuming you have already seeded Datasets)
        $datasets = Dataset::all();

        // For each dataset, create DatasetProperty relationships
        foreach ($datasets as $dataset) {
            // Link PropertyValues to the DatasetProperty (Randomly or in a predefined way)

            // You can link PropertyValues to a dataset (Example: Linking Languages, Centuries, Categories)
            $dataset->properties()->createMany([
                [
                    'property_value_id' => PropertyValue::where('value', 'English')->first()->id,
                    'dataset_id' => $dataset->id,
                ],
                [
                    'property_value_id' => PropertyValue::where('value', '19th Century')->first()->id,
                    'dataset_id' => $dataset->id,
                ],
                [
                    'property_value_id' => PropertyValue::where('value', 'Digits')->first()->id,
                    'dataset_id' => $dataset->id,
                ],
            ]);
        }
    }
}
