<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use App\Models\AuditLog;
use App\Models\Sertifikasi;
use App\Models\User;
use App\Services\AsesmenService;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class AsesmenController extends Controller
{

    protected $service;

    public function __construct(AsesmenService $service)
    {
        $this->service = $service;
    }

    public function listMahasiswa(Request $request, Sertifikasi $sertifikasi)
    {
        $result = $this->service->getMahasiswa($request, $sertifikasi);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function bulkInput(Request $request)
    {
        $validated = $request->validate([
            'sertifikasi_id' => 'required|exists:sertifikasi,id',
            'tanggal_asesmen' => 'required|date',
            'asesmens' => 'required|array',
            'asesmens.*.user_id' => 'required|exists:users,id',
            'asesmens.*.status_kompetensi' => 'required|in:Kompeten,Tidak Kompeten,Tidak Hadir',
            'asesmens.*.catatan' => 'nullable|string',
            'asesmens.*.bukti_pendukung' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        $result = $this->service->bulkInput($request, $validated);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => $result['message']
        ]);
    }
}
