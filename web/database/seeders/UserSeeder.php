<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@savve.com'],
            [
                'name'     => 'Admin Savve',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir1@savve.com'],
            [
                'name'     => 'Kasir Satu',
                'password' => Hash::make('password123'),
                'role'     => 'kasir',
            ]
        );
    }
}
