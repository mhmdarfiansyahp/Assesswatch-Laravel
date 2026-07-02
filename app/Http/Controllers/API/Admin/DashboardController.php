<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\DashboardKompetensiExport;
use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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

        $summary = [
            'total_mahasiswa' => Asesmens::count(),
            'kompeten' => Asesmens::where(
                'status_kompetensi',
                'Kompeten'
            )->count(),
            'tidak_kompeten' => Asesmens::where(
                'status_kompetensi',
                'Tidak Kompeten'
            )->count(),
            'tidak_hadir' => Asesmens::where(
                'status_kompetensi',
                'Tidak Hadir'
            )->count(),
            'belum_dinilai' => Asesmens::whereNull(
                'status_kompetensi'
            )->count(),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $data
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filename =
            'laporan-kompetensi-' .
            now()->format('Y-m-d_H-i-s') .
            '.xlsx';

        return Excel::download(
            new DashboardKompetensiExport(
                $request->tahun,
                $request->sertifikasi_id,
                $request->prodi_id
            ),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $data = (new DashboardKompetensiExport(
            $request->tahun,
            $request->sertifikasi_id,
            $request->prodi_id
        ))->collection();

        $pdf = Pdf::loadView(
            'pdf.dashboard-pdf',
            [
                'data' => $data
            ]
        );

        $filename =
            'laporan-kompetensi-' .
            now()->format('Y-m-d_H-i-s') .
            '.pdf';

        return $pdf->download($filename);
    }
}
