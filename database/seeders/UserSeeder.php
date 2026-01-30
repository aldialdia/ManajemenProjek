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
            'email' => 'superadmin@manajemenprojek.com',
            'status' => 'active',
            'role' => UserRole::SUPER_ADMIN,
            'password' => bcrypt('admin123'),
        ]);

        // Create regular user account
        User::create([
            'name' => 'hakim',
            'email' => 'hakim@gmail.com',
            'status' => 'active',
            'role' => UserRole::USER,
            'password' => bcrypt('revin123'),
        ]);
    }
}
