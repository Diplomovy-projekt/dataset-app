<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Mizu',
            'email' => 'mvrbovsky0@gmail.com',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('asd'),
        ]);

        User::factory()
            ->count(3)
            ->create();

    }
}
