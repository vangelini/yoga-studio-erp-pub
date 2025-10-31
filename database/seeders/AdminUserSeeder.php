<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@shantisadhana.local'],
            [
                'name' => 'Administrator',
                'password_hash' => Hash::make('admin'),
                'role' => 'Admin',
                'status' => 'active',
                'telephone' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
