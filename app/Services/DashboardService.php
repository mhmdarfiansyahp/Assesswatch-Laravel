<?php

namespace App\Services;

use App\Models\Asesmens;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKompetensiPerProdi($tahun = null, $sertifikasiId = null, $prodiId = null)
    {
        $query = Asesmens::query()
            ->join('users', 'asesmens.user_id', '=', 'users.id')
            ->join('prodi', 'users.prodi_id', '=', 'prodi.id')
            ->join('sertifikasi', 'asesmens.sertifikasi_id', '=', 'sertifikasi.id');

        if ($tahun) {
            $query->whereYear('asesmens.tanggal_asesmen', $tahun);
        }

        if ($sertifikasiId) {
            $query->where('asesmens.sertifikasi_id', $sertifikasiId);
        }

        if ($prodiId) {
            $query->where('users.prodi_id', $prodiId);
        }

        $data = (clone $query)
            ->select(
                'prodi.nama_prodi',
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Kompeten' THEN 1 ELSE 0 END) as kompeten"),
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Tidak Kompeten' THEN 1 ELSE 0 END) as tidak_kompeten"),
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Tidak Hadir' THEN 1 ELSE 0 END) as tidak_hadir")
            )
            ->groupBy('prodi.id', 'prodi.nama_prodi')
            ->get();

        $summary = [
            'total_mahasiswa' => (clone $query)->count(),

            'kompeten' => (clone $query)
                ->where('asesmens.status_kompetensi', 'Kompeten')
                ->count(),

            'tidak_kompeten' => (clone $query)
                ->where('asesmens.status_kompetensi', 'Tidak Kompeten')
                ->count(),

            'tidak_hadir' => (clone $query)
                ->where('asesmens.status_kompetensi', 'Tidak Hadir')
                ->count(),

            'belum_dinilai' => User::where('role', 'mahasiswa')
                ->where('prodi_id', $prodiId)
                ->whereDoesntHave('asesmens', function ($q) use ($sertifikasiId) {
                    $q->where('sertifikasi_id', $sertifikasiId);
                })
                ->count(),
        ];

        return [
            'summary' => $summary,
            'data' => $data,
        ];
    }
}
