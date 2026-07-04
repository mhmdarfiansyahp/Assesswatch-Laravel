<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\DashboardKompetensiExport;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function kompetensiPerProdi(Request $request)
    {
        $result = $this->dashboardService->getKompetensiPerProdi(
            $request->tahun,
            $request->sertifikasi_id,
            $request->prodi_id
        );

        return response()->json([
            'success' => true,
            'summary' => $result['summary'],
            'data' => $result['data'],
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

        $pdf = Pdf::loadView('pdf.dashboard-pdf', [
            'data' => $data
        ]);

        $filename =
            'laporan-kompetensi-' .
            now()->format('Y-m-d_H-i-s') .
            '.pdf';

        return $pdf->download($filename);
    }
}
