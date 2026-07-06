<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sertifikasi;
use App\Services\AsesmenService;
use Illuminate\Http\Request;

class AsesmenController extends Controller
{
    protected AsesmenService $service;

    public function __construct(AsesmenService $service)
    {
        $this->service = $service;
    }

    public function listMahasiswa(
        Request $request,
        Sertifikasi $sertifikasi
    ) {
        $result = $this->service->getMahasiswa(
            $request,
            $sertifikasi
        );

        return response()->json(
            $result['status']
                ? $result['data']
                : ['message' => $result['message']],
            $result['code'] ?? 200
        );
    }

    public function bulkInput(Request $request)
    {
        $validated = $request->validate([
            'sertifikasi_id' => 'required|exists:sertifikasi,id',
            'tanggal_asesmen' => 'required|date',

            'asesmens' => 'required|array',

            'asesmens.*.user_id' =>
            'required|exists:users,id',

            'asesmens.*.status_kompetensi' =>
            'required|in:Kompeten,Tidak Kompeten,Tidak Hadir',

            'asesmens.*.catatan' =>
            'nullable|string',

            'asesmens.*.bukti_pendukung' =>
            'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        $result = $this->service->bulkInput(
            $request,
            $validated
        );

        return response()->json([
            'message' => $result['message']
        ], $result['code']);
    }
}
