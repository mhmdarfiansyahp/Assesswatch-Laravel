<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sertifikasi;
use Illuminate\Http\Request;

class SertifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Sertifikasi::with('prodi')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama_sertifikasi' => 'nullable|string|max:100',
            'lembaga' => 'nullable|string|max:100',
            'level' => 'nullable|in:Nasional,Internasional',
            'tanggal_sertifikasi' => 'required|date',
        ]);

        $sertifikasi = Sertifikasi::create([
            'prodi_id' => $request->prodi_id,
            'nama_sertifikasi' => $request->nama_sertifikasi,
            'lembaga' => $request->lembaga,
            'level' => $request->level,
            'tanggal_sertifikasi' => $request->tanggal_sertifikasi,
            'verification_code' => Str::uuid(),
            'status' => true,
        ]);

        return response()->json($sertifikasi, 201);
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
        $sertifikasi->update($request->all());

        return response()->json($sertifikasi);
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
}
