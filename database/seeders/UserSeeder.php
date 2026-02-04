<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin account
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'status' => 'active',
            'role' => UserRole::SUPER_ADMIN,
            'password' => bcrypt('admin123'),
        ]);
    }
}
