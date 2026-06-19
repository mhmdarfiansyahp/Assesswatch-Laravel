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
            'name' => 'Admin Utama',
            'username' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'prodi_id' => null,
            'status' => true,
        ]);

        User::create([
            'name' => 'Instruktur Satu',
            'username' => 'instruktur1',
            'email' => 'instruktur@mail.com',
            'password' => Hash::make('password'),
            'role' => 'instruktur',
            'prodi_id' => null,
            'status' => true,
        ]);

        User::create([
            'name' => 'Mahasiswa Satu',
            'username' => 'mahasiswa1',
            'email' => 'mahasiswa@mail.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi_id' => 1,
            'status' => true,
        ]);
    }
}
