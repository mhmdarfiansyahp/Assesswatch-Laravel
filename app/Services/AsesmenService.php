<?php

namespace App\Services;

use App\Models\Asesmens;
use App\Models\AuditLog;
use App\Models\Sertifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AsesmenService
{
    public function getMahasiswa(Request $request, Sertifikasi $sertifikasi)
    {
        $instruktur = $request->user();

        if ($instruktur->role !== 'instruktur') {
            return [
                'status' => false,
                'code' => 403,
                'message' => 'Unauthorized'
            ];
        }

        $hasAccess = DB::table('instruktur_prodi')
            ->where('instruktur_id', $instruktur->id)
            ->where('prodi_id', $sertifikasi->prodi_id)
            ->exists();

        if (!$hasAccess) {
            return [
                'status' => false,
                'code' => 403,
                'message' => 'Anda tidak memiliki akses'
            ];
        }

        $mahasiswa = User::query()
            ->leftJoin('asesmens', function ($join) use ($sertifikasi) {
                $join->on('users.id', '=', 'asesmens.user_id')
                    ->where('asesmens.sertifikasi_id', $sertifikasi->id);
            })
            ->where('users.role', 'mahasiswa')
            ->where('users.prodi_id', $sertifikasi->prodi_id)
            ->where(function ($q) {
                $q->whereNull('asesmens.status_kompetensi')
                    ->orWhere('asesmens.status_kompetensi', '!=', 'Kompeten');
            })
            ->select([
                'users.id',
                'users.nim',
                'users.name as nama',
                DB::raw('COALESCE(asesmens.status_kompetensi, "") as status')
            ])
            ->get();

        return [
            'status' => true,
            'data' => [
                'message' => 'Data mahasiswa',
                'sertifikasi' => [
                    'id' => $sertifikasi->id,
                    'nama_sertifikasi' => $sertifikasi->nama_sertifikasi,
                    'lembaga' => $sertifikasi->lembaga,
                    'level' => $sertifikasi->level,
                ],
                'data' => $mahasiswa
            ]
        ];
    }

    public function bulkInput(Request $request, array $validated)
    {
        $instruktur = $request->user();

        if ($instruktur->role !== 'instruktur') {
            return [
                'status' => false,
                'code' => 403,
                'message' => 'Unauthorized'
            ];
        }

        $sertifikasi = Sertifikasi::findOrFail($validated['sertifikasi_id']);

        $hasAccess = DB::table('instruktur_prodi')
            ->where('instruktur_id', $instruktur->id)
            ->where('prodi_id', $sertifikasi->prodi_id)
            ->exists();

        if (!$hasAccess) {
            return [
                'status' => false,
                'code' => 403,
                'message' => 'Anda tidak memiliki akses ke sertifikasi ini'
            ];
        }

        $userIds = collect($validated['asesmens'])
            ->pluck('user_id')
            ->unique()
            ->values();

        $users = User::whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $asesmens = Asesmens::where('sertifikasi_id', $sertifikasi->id)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $uploadedFiles = $request->file('asesmens', []);

        $auditLogs = [];

        DB::beginTransaction();

        try {

            foreach ($validated['asesmens'] as $index => $item) {

                $mahasiswa = $users->get($item['user_id']);

                if (!$mahasiswa || $mahasiswa->role !== 'mahasiswa') {
                    throw new \Exception("User tidak valid");
                }

                if ($mahasiswa->prodi_id != $sertifikasi->prodi_id) {
                    throw new \Exception("Mahasiswa beda prodi");
                }

                $asesmen = $asesmens->get($mahasiswa->id);

                $oldValues = $asesmen?->toArray();

                $certificateCode = null;

                if ($item['status_kompetensi'] === 'Kompeten') {

                    if ($asesmen && $asesmen->certificate_code) {
                        $certificateCode = $asesmen->certificate_code;
                    } else {
                        $certificateCode = CertificateService::generateCertificateCode($sertifikasi);
                    }
                }

                $filePath = $asesmen?->bukti_pendukung;

                $file = $uploadedFiles[$index]['bukti_pendukung'] ?? null;

                if ($file) {

                    if ($filePath) {
                        Storage::disk('public')->delete($filePath);
                    }

                    $filePath = $file->store('bukti-sertifikasi', 'public');
                }

                if (
                    $item['status_kompetensi'] === 'Kompeten'
                    && !$file
                    && !$asesmen?->bukti_pendukung
                ) {
                    throw new \Exception("Bukti sertifikasi wajib untuk mahasiswa kompeten");
                }

                $data = [
                    'status_kompetensi' => $item['status_kompetensi'],
                    'catatan' => $item['catatan'] ?? null,
                    'tanggal_asesmen' => $validated['tanggal_asesmen'],
                    'instruktur_id' => $instruktur->id,
                    'certificate_code' => $certificateCode ?? $asesmen?->certificate_code,
                    'bukti_pendukung' => $filePath,
                ];

                if ($asesmen) {
                    $asesmen->update($data);
                } else {
                    $data['user_id'] = $mahasiswa->id;
                    $data['sertifikasi_id'] = $sertifikasi->id;
                    $asesmen = Asesmens::create($data);
                }

                $auditLogs[] = [
                    'user_id' => $instruktur->id,
                    'action' => $oldValues ? 'UPDATE_ASESMEN' : 'CREATE_ASESMEN',
                    'table_name' => 'asesmens',
                    'record_id' => $asesmen->id,
                    'old_values' => $oldValues ? json_encode($oldValues) : null,
                    'new_values' => json_encode($asesmen->fresh()->toArray()),
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($auditLogs)) {
                AuditLog::insert($auditLogs);
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Input asesmen berhasil'
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'status' => false,
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }
}
