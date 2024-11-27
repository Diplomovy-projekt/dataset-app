<?php

namespace Database\Seeders;

use App\Models\AnnotationFormat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnotationFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some AnnotationFormat entries
        AnnotationFormat::create([
            'name' => 'YOLO',
            'extension' => 'txt',
        ]);

        AnnotationFormat::create([
            'name' => 'COCO',
            'extension' => 'json',
        ]);

        AnnotationFormat::create([
            'name' => 'Pascal VOC',
            'extension' => 'xml',
        ]);

        AnnotationFormat::create([
            'name' => 'CSV',
            'extension' => 'csv',
        ]);
    }
}
