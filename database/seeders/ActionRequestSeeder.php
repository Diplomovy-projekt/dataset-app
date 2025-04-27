<?php

namespace Database\Seeders;

use App\Models\ActionRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ActionRequest::factory()->count(15)->pending()->create();
        ActionRequest::factory()->count(5)->approved()->create();
        ActionRequest::factory()->count(5)->rejected()->create();
    }
}
