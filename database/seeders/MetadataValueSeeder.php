<?php

namespace Database\Seeders;

use App\Models\MetadataType;
use App\Models\MetadataValue;
use Illuminate\Database\Seeder;

class MetadataValueSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch MetadataTypes
        $languageType = MetadataType::where('name', 'Language')->first();
        $centuryType = MetadataType::where('name', 'Century')->first();
        $imageGranularityType = MetadataType::where('name', 'Image Granularity')->first();
        $datasetPartitionType = MetadataType::where('name', 'Dataset Partition')->first();


        if (!$languageType || !$centuryType) {
            throw new \Exception('One or more MetadataTypes not found.');
        }

        $values = [
            ['metadata_type_id' => $languageType->id, 'value' => 'English'],
            ['metadata_type_id' => $languageType->id, 'value' => 'French'],
            ['metadata_type_id' => $languageType->id, 'value' => 'German'],
            ['metadata_type_id' => $languageType->id, 'value' => 'Hungarian'],
            ['metadata_type_id' => $languageType->id, 'value' => 'Italian'],
            ['metadata_type_id' => $languageType->id, 'value' => 'Latin'],
            ['metadata_type_id' => $languageType->id, 'value' => 'Spanish'],
            ['metadata_type_id' => $centuryType->id, 'value' => '15th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '16th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '17th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '18th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '19th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '20th Century'],
            ['metadata_type_id' => $imageGranularityType->id, 'value' => 'Whole (Original)'],
            ['metadata_type_id' => $imageGranularityType->id, 'value' => 'Sub-region (Crop)'],
            ['metadata_type_id' => $datasetPartitionType->id, 'value' => 'Train'],
            ['metadata_type_id' => $datasetPartitionType->id, 'value' => 'Test'],
            ['metadata_type_id' => $datasetPartitionType->id, 'value' => 'Valid'],
            ['metadata_type_id' => $datasetPartitionType->id, 'value' => 'Unpartitioned'],
        ];

        foreach ($values as $value) {
            MetadataValue::firstOrCreate(
                [
                    'metadata_type_id' => $value['metadata_type_id'],
                    'value' => $value['value'],
                ]
            );
        }
    }
}
