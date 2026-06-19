<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        Prodi::create([
            'nama_prodi' => 'Teknik Informatika',
            'status' => true,
        ]);

        Prodi::create([
            'nama_prodi' => 'Sistem Informasi',
            'status' => true,
        ]);
    }
}