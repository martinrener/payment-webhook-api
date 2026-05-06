<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Martin',
            'email' => 'martin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_admin' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Matias',
            'email' => 'matias@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_admin' => false,
        ]);
    }
}
