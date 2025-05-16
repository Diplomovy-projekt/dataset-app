<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MetadataTypeSeeder::class,
            MetadataValueSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        if (App::environment() == 'local') {
            $this->call([
                InvitationSeeder::class,
                ActionRequestSeeder::class,
            ]);
        }
    }

}
