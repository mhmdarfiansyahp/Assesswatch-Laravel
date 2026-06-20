<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SertifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sertifikasi')->insert([
            [
                'prodi_id' => 1,
                'nama_sertifikasi' => 'AWS Cloud Practitioner',
                'lembaga' => 'Amazon Web Services',
                'level' => 'Internasional',
                'tanggal_sertifikasi' => '2024-06-10',
                'verification_code' => Str::uuid(),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prodi_id' => 1,
                'nama_sertifikasi' => 'Cisco Networking Basics',
                'lembaga' => 'Cisco',
                'level' => 'Internasional',
                'tanggal_sertifikasi' => '2024-05-12',
                'verification_code' => Str::uuid(),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prodi_id' => 2,
                'nama_sertifikasi' => 'UI/UX Design Fundamentals',
                'lembaga' => 'Google',
                'level' => 'Internasional',
                'tanggal_sertifikasi' => '2024-04-20',
                'verification_code' => Str::uuid(),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prodi_id' => 2,
                'nama_sertifikasi' => 'Pelatihan Web Development',
                'lembaga' => 'BNSP',
                'level' => 'Nasional',
                'tanggal_sertifikasi' => '2024-03-15',
                'verification_code' => Str::uuid(),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
