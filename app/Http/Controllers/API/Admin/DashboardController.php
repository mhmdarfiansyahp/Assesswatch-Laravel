<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Asesmens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function kompetensiPerProdi(Request $request)
    {
        $tahun = $request->tahun;
        $sertifikasiId = $request->sertifikasi_id;
        $prodiId = $request->prodi_id;

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
        $data = $query
            ->select(
                'prodi.nama_prodi',
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Kompeten' THEN 1 ELSE 0 END) as kompeten"),
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Tidak Kompeten' THEN 1 ELSE 0 END) as tidak_kompeten"),
                DB::raw("SUM(CASE WHEN asesmens.status_kompetensi = 'Tidak Hadir' THEN 1 ELSE 0 END) as tidak_hadir")
            )
            ->groupBy('prodi.id', 'prodi.nama_prodi')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
