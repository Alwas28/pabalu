<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@pabalu.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        // Buat profile untuk admin
        Profile::firstOrCreate(
            ['email' => 'admin@pabalu.com'],
            [
                'user_id'      => $user->id,
                'nama_lengkap' => 'Administrator',
                'jabatan'      => 'Administrator Sistem',
            ]
        );

        // Assign role admin
        $user->assignRole('admin');
    }
}
