<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asesmens;
use App\Models\AuditLog;
use App\Models\Sertifikasi;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsesmenController extends Controller
{
    public function listMahasiswa(
        Request $request,
        Sertifikasi $sertifikasi
    ) {
        $instruktur = $request->user();

        if ($instruktur->role !== 'instruktur') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $hasAccess = DB::table('instruktur_prodi')
            ->where('instruktur_id', $instruktur->id)
            ->where('prodi_id', $sertifikasi->prodi_id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }

        $asesmenMap = Asesmens::where('sertifikasi_id', $sertifikasi->id)
            ->get()
            ->keyBy('user_id');

        $mahasiswa = User::where('role', 'mahasiswa')
            ->where('prodi_id', $sertifikasi->prodi_id)
            ->get()
            ->map(function ($mhs) use ($asesmenMap) {

                $asesmen = $asesmenMap[$mhs->id] ?? null;

                $status = $asesmen?->status_kompetensi ?? "";

                return [
                    'id' => $mhs->id,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->name,
                    'status' => $status,
                ];
            });

        return response()->json([
            'message' => 'Data mahasiswa',

            'sertifikasi' => [
                'id' => $sertifikasi->id,
                'nama_sertifikasi' => $sertifikasi->nama_sertifikasi,
                'lembaga' => $sertifikasi->lembaga,
                'level' => $sertifikasi->level,
            ],

            'data' => $mahasiswa
        ]);
    }

    public function bulkInput(Request $request)
    {
        $instruktur = $request->user();
        if ($instruktur->role !== 'instruktur') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['sertifikasi_id' => 'required|exists:sertifikasi,id', 'tanggal_asesmen' => 'required|date', 'asesmens' => 'required|array', 'asesmens.*.user_id' => 'required|exists:users,id', 'asesmens.*.status_kompetensi' => 'required|in:Kompeten,Tidak Kompeten,Tidak Hadir', 'asesmens.*.catatan' => 'nullable|string',]);
        $sertifikasi = Sertifikasi::findOrFail($validated['sertifikasi_id']);

        $hasAccess = DB::table('instruktur_prodi')
            ->where('instruktur_id', $instruktur->id)
            ->where('prodi_id', $sertifikasi->prodi_id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke sertifikasi ini'
            ], 403);
        }

        DB::beginTransaction();

        try {
            foreach ($validated['asesmens'] as $item) {
                $mahasiswa = User::where('id', $item['user_id'])->first();

                if (!$mahasiswa || $mahasiswa->role !== 'mahasiswa') {
                    throw new \Exception("User tidak valid: " . $item['user_id']);
                }

                if ($mahasiswa->prodi_id != $sertifikasi->prodi_id) {
                    throw new \Exception("Mahasiswa beda prodi: " . $item['user_id']);
                }
                $asesmen = Asesmens::where('user_id', $mahasiswa->id)->where('sertifikasi_id', $sertifikasi->id)->first();
                $oldValues = $asesmen ? $asesmen->toArray() : null;

                $certificateCode = null;

                if ($item['status_kompetensi'] === 'Kompeten') {

                    if ($asesmen && $asesmen->certificate_code) {

                        $certificateCode = $asesmen->certificate_code;
                    } else {

                        $certificateCode = CertificateService::generateCertificateCode($sertifikasi);
                    }
                }

                if ($asesmen) {
                    $asesmen->update([
                        'status_kompetensi' => $item['status_kompetensi'],
                        'catatan' => $item['catatan'] ?? null,
                        'tanggal_asesmen' => $validated['tanggal_asesmen'],
                        'instruktur_id' => $instruktur->id,
                        'certificate_code' => $certificateCode ?? $asesmen?->certificate_code,
                    ]);
                } else {
                    $asesmen = Asesmens::create([
                        'user_id' => $mahasiswa->id,
                        'sertifikasi_id' => $sertifikasi->id,
                        'instruktur_id' => $instruktur->id,
                        'status_kompetensi' => $item['status_kompetensi'],
                        'catatan' => $item['catatan'] ?? null,
                        'tanggal_asesmen' => $validated['tanggal_asesmen'],
                        'certificate_code' => $certificateCode ?? $asesmen?->certificate_code,
                    ]);
                }

                AuditLog::create(['user_id' => $instruktur->id, 'action' => $oldValues ? 'UPDATE_ASESMEN' : 'CREATE_ASESMEN', 'table_name' => 'asesmens', 'record_id' => $asesmen->id, 'old_values' => $oldValues ? json_encode($oldValues) : null, 'new_values' => json_encode($asesmen->fresh()->toArray()), 'ip_address' => $request->ip(),]);
            }
            DB::commit();
            return response()->json(['message' => 'Input asesmen berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan', 'error' => $e->getMessage()], 500);
        }
    }
}
