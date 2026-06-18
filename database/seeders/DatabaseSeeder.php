<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(OutletTypesSeeder::class);
        $this->call(ProvinceSeeder::class);

        User::create([
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'password'          => 'password',
            'email_verified_at' => now(),
        ]);
    }
}
