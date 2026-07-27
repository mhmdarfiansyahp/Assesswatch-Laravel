<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
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
        try {
            $user = $request->user();

            // 1. Otorisasi kepemilikan data
            if ((int)$asesmen->user_id !== (int)$user->id) {
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

            // 3. Ambil kode sertifikat & buat URL untuk React Frontend
            $code = $asesmen->certificate_code ?? 'INVALID-CODE';
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            $qrValue = "{$frontendUrl}/verify-certificate?code={$code}";

            // Generate QR Code format SVG base64
            $qrCode = base64_encode(
                QrCode::format('svg')
                    ->size(140)
                    ->errorCorrection('H')
                    ->generate($qrValue)
            );

            // 4. Render file Blade menjadi PDF
            $pdf = Pdf::loadView('pdf.surat-keterangan-kompetensi', [
                'asesmen' => $asesmen,
                'qrCode'  => $qrCode
            ])->setPaper('a4', 'portrait');

            // 5. Download file dengan nama yang dinamis
            $namaUser = $user->name ?? $user->nama ?? 'Mahasiswa';
            $fileName = 'Surat_Keterangan_Kompetensi_' . str_replace(' ', '_', $namaUser) . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('PDF Download Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal mengunduh sertifikat: ' . $e->getMessage()
            ], 500);
        }
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
