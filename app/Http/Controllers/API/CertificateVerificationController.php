<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    public function verify($code)
    {
        $asesmen = Asesmens::with(['user', 'sertifikasi'])
            ->where('certificate_code', strtoupper(trim($code)))
            ->first();

        if (!$asesmen) {
            return response()->json([
                'message' => 'Nomor sertifikat tidak ditemukan atau tidak terdaftar.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'certificate_number' => $asesmen->certificate_code,
                'student_name'       => $asesmen->user->name ?? '-',
                'competency_name'   => $asesmen->sertifikasi->nama_sertifikasi ?? '-',
                'issue_date'        => $asesmen->tanggal_asesmen
                    ? \Carbon\Carbon::parse($asesmen->tanggal_asesmen)->isoFormat('D MMMM Y')
                    : '-',
                'status'            => $asesmen->status_kompetensi === 'Kompeten' ? 'valid' : 'invalid',
            ]
        ], 200);
    }
}
