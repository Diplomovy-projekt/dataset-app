<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Digits', 'Letters', 'Words', 'Sentences', 'Glyphs', 'Cipher keys'];
        foreach($categories as $category){
            \App\Models\Category::create([
                'name' => $category
            ]);
        }
    }
}
