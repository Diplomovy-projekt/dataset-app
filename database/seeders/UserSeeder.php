<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment() === 'prod') {
            User::firstOrCreate(
                ['email' => 'admin@hcportal.eu'],
                [
                    'name' => 'HCPortal',
                    'role' => 'admin',
                    'is_active' => true,
                    'password' => Hash::make('hcportal'),
                ]
            );
        } else {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'role' => 'admin',
                    'is_active' => true,
                    'password' => Hash::make('password'),
                ]
            );

            User::firstOrCreate(
                ['email' => 'mvrbovsky0@gmail.com'],
                [
                    'name' => 'Mizu',
                    'role' => 'admin',
                    'is_active' => true,
                    'password' => Hash::make('asd'),
                ]
            );

            User::firstOrCreate(
                ['email' => 'john.doe@example.com'],
                [
                    'name' => 'John Doe',
                    'role' => 'user',
                    'is_active' => true,
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
