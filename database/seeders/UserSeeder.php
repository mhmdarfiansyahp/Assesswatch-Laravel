<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA PRODI
        |--------------------------------------------------------------------------
        */
        $teknikInformatika = Prodi::where('nama_prodi', 'Teknik Informatika')->first();
        $sistemInformasi = Prodi::where('nama_prodi', 'Sistem Informasi')->first();

        /*
        |--------------------------------------------------------------------------
        | ADMIN (Tetap Menggunakan Nama Default Anda)
        |--------------------------------------------------------------------------
        */
        User::create([
            'name' => 'Admin Utama',
            'username' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'prodi_id' => null,
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | INSTRUKTUR (1 User = 1 Prodi di Tabel Pivot)
        |--------------------------------------------------------------------------
        */
        $instruktur1 = User::create([
            'name' => 'Dr. Budi Darmawan, M.T.',
            'username' => 'budi_darmawan',
            'email' => 'budi.darmawan@mail.com',
            'password' => Hash::make('password'),
            'role' => 'instruktur',
            'prodi_id' => null,
            'nip' => '198812312024001',
            'status' => true,
        ]);

        $instruktur2 = User::create([
            'name' => 'Siti Aminah, M.Kom.',
            'username' => 'siti_aminah',
            'email' => 'siti.aminah@mail.com',
            'password' => Hash::make('password'),
            'role' => 'instruktur',
            'prodi_id' => null,
            'nip' => '198812312024002',
            'status' => true,
        ]);

        DB::table('instruktur_prodi')->insert([
            [
                'instruktur_id' => $instruktur1->id,
                'prodi_id' => $teknikInformatika->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'instruktur_id' => $instruktur2->id,
                'prodi_id' => $sistemInformasi->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | MAHASISWA TEKNIK INFORMATIKA (5 Users)
        |--------------------------------------------------------------------------
        */
        $mhsTI = [
            ['name' => 'Rian Hidayat', 'username' => 'rian_hidayat', 'email' => 'rian.h@mail.com', 'nim' => '221011400001'],
            ['name' => 'Aditya Pratama', 'username' => 'aditya_pratama', 'email' => 'aditya.p@mail.com', 'nim' => '221011400002'],
            ['name' => 'Dinda Lestari', 'username' => 'dinda_lestari', 'email' => 'dinda.l@mail.com', 'nim' => '221011400003'],
            ['name' => 'Fikri Haikal', 'username' => 'fikri_haikal', 'email' => 'fikri.h@mail.com', 'nim' => '221011400004'],
            ['name' => 'Amalia Rosa', 'username' => 'amalia_rosa', 'email' => 'amalia.r@mail.com', 'nim' => '221011400005'],
        ];

        foreach ($mhsTI as $mhs) {
            User::create([
                'name' => $mhs['name'],
                'username' => $mhs['username'],
                'email' => $mhs['email'],
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'prodi_id' => $teknikInformatika->id,
                'nim' => $mhs['nim'],
                'status' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAHASISWA SISTEM INFORMASI (5 Users)
        |--------------------------------------------------------------------------
        */
        $mhsSI = [
            ['name' => 'Reza Pahlevi', 'username' => 'reza_pahlevi', 'email' => 'reza.p@mail.com', 'nim' => '221011500001'],
            ['name' => 'Nadia Utami', 'username' => 'nadia_utami', 'email' => 'nadia.u@mail.com', 'nim' => '221011500002'],
            ['name' => 'Dimas Saputra', 'username' => 'dimas_saputra', 'email' => 'dimas.s@mail.com', 'nim' => '221011500003'],
            ['name' => 'Putri Wulandari', 'username' => 'putri_wulandari', 'email' => 'putri.w@mail.com', 'nim' => '221011500004'],
            ['name' => 'Eko Prasetyo', 'username' => 'eko_prasetyo', 'email' => 'eko.p@mail.com', 'nim' => '221011500005'],
        ];

        foreach ($mhsSI as $mhs) {
            User::create([
                'name' => $mhs['name'],
                'username' => $mhs['username'],
                'email' => $mhs['email'],
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'prodi_id' => $sistemInformasi->id,
                'nim' => $mhs['nim'],
                'status' => true,
            ]);
        }
    }
}
