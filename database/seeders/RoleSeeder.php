<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::updateOrCreate(
            ['email' => 'anuradha@gmail.com'],
            [
                'name' => 'Super Admin Anu',
                'password' => Hash::make('AdminAnu'), // change as needed
                'role' => 'superadmin',
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('AdminPass123'),
                'role' => 'admin',
            ]
        );

        // Member
        User::updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'password' => Hash::make('MemberPass123'),
                'role' => 'member',
            ]
        );
    }
}
