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
            'name' => 'HcPortal',
            'email' => 'admin@hcportal.eu',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('hcportal'),
        ]);

        User::create([
            'name' => 'Mizu',
            'email' => 'mvrbovsky0@gmail.com',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('asd'),
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'role' => 'user',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'user',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Alice Brown',
            'email' => 'alice.brown@example.com',
            'role' => 'user',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

    }
}
