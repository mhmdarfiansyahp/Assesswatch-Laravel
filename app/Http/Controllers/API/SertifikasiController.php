<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SertifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sertifikasi = Sertifikasi::query()
            ->select([
                'id',
                'prodi_id',
                'nama_sertifikasi',
                'lembaga',
                'level',
                'tanggal_sertifikasi',
                'scheme_code',
                'status'
            ])
            ->with([
                'prodi:id,nama_prodi'
            ])
            ->paginate(10);

        return response()->json($sertifikasi);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama_sertifikasi' => 'required|string|max:100',
            'lembaga' => 'required|string|max:100',
            'level' => 'required|in:Nasional,Internasional',
            'tanggal_sertifikasi' => 'required|date',
        ]);

        do {
            $schemeCode = 'SCM-' . strtoupper(Str::random(8));
        } while (
            Sertifikasi::where('scheme_code', $schemeCode)->exists()
        );

        $sertifikasi = Sertifikasi::create([
            'prodi_id' => $request->prodi_id,
            'nama_sertifikasi' => $request->nama_sertifikasi,
            'lembaga' => $request->lembaga,
            'level' => $request->level,
            'tanggal_sertifikasi' => $request->tanggal_sertifikasi,
            'scheme_code' => $schemeCode,
            'status' => true,
        ]);

        return response()->json([
            'message' => 'Sertifikasi created',
            'data' => $sertifikasi
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(
            Sertifikasi::with('prodi')->findOrFail($id)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sertifikasi = Sertifikasi::findOrFail($id);
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama_sertifikasi' => 'required|string|max:100',
            'lembaga' => 'required|string|max:100',
            'level' => 'required|in:Nasional,Internasional',
            'tanggal_sertifikasi' => 'required|date',
            'status' => 'required|boolean',
        ]);

        $sertifikasi->update([
            'prodi_id' => $request->prodi_id,
            'nama_sertifikasi' => $request->nama_sertifikasi,
            'lembaga' => $request->lembaga,
            'level' => $request->level,
            'tanggal_sertifikasi' => $request->tanggal_sertifikasi,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Sertifikasi updated',
            'data' => $sertifikasi
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sertifikasi = Sertifikasi::findOrFail($id);
        $sertifikasi->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function getSertifikasi(Request $request)
    {
        $instruktur = $request->user();

        if ($instruktur->role !== 'instruktur') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $prodiIds = DB::table('instruktur_prodi')
            ->where('instruktur_id', $instruktur->id)
            ->pluck('prodi_id');

        $sertifikasi = Sertifikasi::with('prodi')
            ->whereIn('prodi_id', $prodiIds)
            ->get();

        return response()->json([
            'message' => 'Daftar sertifikasi instruktur',
            'data' => $sertifikasi
        ]);
    }
}
