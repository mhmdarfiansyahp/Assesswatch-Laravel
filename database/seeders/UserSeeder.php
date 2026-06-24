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
        | Ambil prodi
        |--------------------------------------------------------------------------
        */

        $teknikInformatika = Prodi::where(
            'nama_prodi',
            'Teknik Informatika'
        )->first();

        $sistemInformasi = Prodi::where(
            'nama_prodi',
            'Sistem Informasi'
        )->first();

        /*
        |--------------------------------------------------------------------------
        | ADMIN
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
        | INSTRUKTUR
        |--------------------------------------------------------------------------
        */

        $instruktur1 = User::create([
            'name' => 'Instruktur Satu',
            'username' => 'instruktur1',
            'email' => 'instruktur1@mail.com',
            'password' => Hash::make('password'),
            'role' => 'instruktur',
            'prodi_id' => null,
            'nip' => '198812312024001',
            'status' => true,
        ]);

        $instruktur2 = User::create([
            'name' => 'Instruktur Dua',
            'username' => 'instruktur2',
            'email' => 'instruktur2@mail.com',
            'password' => Hash::make('password'),
            'role' => 'instruktur',
            'prodi_id' => null,
            'nip' => '198812312024002',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ASSIGN PRODI KE INSTRUKTUR
        |--------------------------------------------------------------------------
        */

        DB::table('instruktur_prodi')->insert([

            [
                'instruktur_id' => $instruktur1->id,
                'prodi_id' => $teknikInformatika->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'instruktur_id' => $instruktur1->id,
                'prodi_id' => $sistemInformasi->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'instruktur_id' => $instruktur2->id,
                'prodi_id' => $teknikInformatika->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | MAHASISWA TI
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Mahasiswa TI 1',
            'username' => 'mhs_ti_1',
            'email' => 'mhs_ti_1@mail.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi_id' => $teknikInformatika->id,
            'nim' => '221011400001',
            'status' => true,
        ]);

        User::create([
            'name' => 'Mahasiswa TI 2',
            'username' => 'mhs_ti_2',
            'email' => 'mhs_ti_2@mail.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi_id' => $teknikInformatika->id,
            'nim' => '221011400002',
            'status' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | MAHASISWA SI
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Mahasiswa SI 1',
            'username' => 'mhs_si_1',
            'email' => 'mhs_si_1@mail.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi_id' => $sistemInformasi->id,
            'nim' => '221011500001',
            'status' => true,
        ]);

        User::create([
            'name' => 'Mahasiswa SI 2',
            'username' => 'mhs_si_2',
            'email' => 'mhs_si_2@mail.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'prodi_id' => $sistemInformasi->id,
            'nim' => '221011500002',
            'status' => true,
        ]);
    }
}
