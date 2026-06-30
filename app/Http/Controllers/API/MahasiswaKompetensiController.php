<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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

        if (!$asesmen->bukti_pendukung) {
            return response()->json([
                'message' => 'File tidak tersedia'
            ], 404);
        }

        $path = storage_path(
            'app/public/' . $asesmen->bukti_pendukung
        );

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        $mime = mime_content_type($path);

        if ($mime === 'application/pdf') {

            return response()->download(
                $path,
                'sertifikat.pdf',
                [
                    'Content-Type' => 'application/pdf'
                ]
            );
        }
        if (str_contains($mime, 'image/')) {
            $imageData = base64_encode(
                file_get_contents($path)
            );

            $src = 'data:' . $mime . ';base64,' . $imageData;
            $pdf = Pdf::loadView(
                'pdf.sertifikat-image',
                [
                    'src' => $src
                ]
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                'sertifikat.pdf'
            );
        }

        return response()->json([
            'message' => 'Format file tidak didukung'
        ], 400);
    }
}
