<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'pemilik@stok.test'], [
            'name' => 'Pemilik Toko', 'role' => 'owner', 'password' => Hash::make('password'),
        ]);
        User::updateOrCreate(['email' => 'gudang@stok.test'], [
            'name' => 'Staf Gudang', 'role' => 'staff', 'password' => Hash::make('password'),
        ]);
    }
}
