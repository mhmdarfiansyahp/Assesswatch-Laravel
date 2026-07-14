<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class MahasiswaKompetensiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = Asesmens::with('sertifikasi')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_sertifikasi' => $item->sertifikasi->nama_sertifikasi,
                    'lembaga' => $item->sertifikasi->lembaga,
                    'level' => $item->sertifikasi->level,
                    'tanggal' => $item->tanggal_asesmen,
                    'status' => $item->status_kompetensi,
                    'certificate_code' => $item->certificate_code,
                    'has_file' => !empty($item->bukti_pendukung),
                ];
            });

        return response()->json([
            'message' => 'Data kompetensi mahasiswa',
            'data' => $data
        ]);
    }

    public function download(Request $request, Asesmens $asesmen)
    {
        $user = $request->user();

        if ($asesmen->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // 2. Pastikan mahasiswa memang sudah dinyatakan "Kompeten"
        if ($asesmen->status_kompetensi !== 'Kompeten') {
            return response()->json([
                'message' => 'Surat keterangan belum tersedia atau Anda belum kompeten.'
            ], 400);
        }

        // Load relasi user dan sertifikasi agar datanya lengkap di PDF
        $asesmen->load(['user', 'sertifikasi']);

        // 3. Generate QR Code (Isinya bisa berupa URL verifikasi atau Kode Sertifikat)
        // Kita convert ke Base64 format PNG agar bisa dibaca dengan baik oleh DomPDF
        $qrValue = route('verifikasi.sertifikat', ['code' => $asesmen->certificate_code]); // Contoh URL verifikasi
        $qrCode = base64_encode(
            QrCode::format('svg')
                ->size(150)
                ->errorCorrection('H')
                ->generate($qrValue)
        );

        // 4. Render file Blade menjadi PDF
        $pdf = Pdf::loadView('pdf.surat-keterangan-kompetensi', [
            'asesmen' => $asesmen,
            'qrCode' => $qrCode
        ])->setPaper('a4', 'portrait'); // Umumnya surat keterangan berbentuk Portrait (Tegak)

        // 5. Download file dengan nama yang dinamis
        $fileName = 'Surat_Keterangan_Kompetensi_' . str_replace(' ', '_', $user->nama) . '.pdf';
        return $pdf->download($fileName);
    }

    public function verifikasi($code)
    {
        // Cari data asesmen berdasarkan certificate_code yang unik
        $asesmen = Asesmens::with(['sertifikasi', 'user'])
            ->where('certificate_code', $code)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'message' => 'Sertifikat Kompetensi Valid',
            'data' => [
                'nama_mahasiswa' => $asesmen->user->name ?? $asesmen->user->nama, // Sesuaikan field nama di tabel users Anda
                'sertifikasi'    => $asesmen->sertifikasi->nama_sertifikasi,
                'lembaga'        => $asesmen->sertifikasi->lembaga,
                'tanggal_lulus'  => $asesmen->tanggal_asesmen,
                'nomor_surat'    => $asesmen->certificate_code,
            ]
        ]);
    }
}
