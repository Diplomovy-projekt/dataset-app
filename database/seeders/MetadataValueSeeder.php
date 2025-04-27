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

        if (!$languageType || !$centuryType) {
            throw new \Exception('One or more MetadataTypes not found.');
        }

        $values = [
            ['metadata_type_id' => $languageType->id, 'value' => 'English'],
            ['metadata_type_id' => $languageType->id, 'value' => 'French'],
            ['metadata_type_id' => $languageType->id, 'value' => 'Spanish'],
            ['metadata_type_id' => $centuryType->id, 'value' => '19th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '20th Century'],
            ['metadata_type_id' => $centuryType->id, 'value' => '21st Century'],
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
