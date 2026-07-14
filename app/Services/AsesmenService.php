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
            ->where('role', 'mahasiswa')
            ->where('prodi_id', $sertifikasi->prodi_id)

            ->whereDoesntHave('asesmens', function ($q) use ($sertifikasi) {
                $q->where('sertifikasi_id', $sertifikasi->id)
                    ->where('status_kompetensi', 'Kompeten');
            })

            ->get()

            ->map(function ($mhs) use ($sertifikasi) {
                $asesmen = Asesmens::query()
                    ->where('user_id', $mhs->id)
                    ->where('sertifikasi_id', $sertifikasi->id)
                    ->first();

                return [
                    'id' => $mhs->id,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->name,

                    'status' => $asesmen?->status_kompetensi ?? '',
                ];
            });

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
                'code' => 403,
                'message' => 'Anda tidak memiliki akses ke sertifikasi ini'
            ];
        }

        DB::beginTransaction();

        try {
            foreach ($validated['asesmens'] as $index => $item) {
                $mahasiswa = User::find($item['user_id']);

                if (!$mahasiswa || $mahasiswa->role !== 'mahasiswa') {
                    throw new \Exception("User tidak valid");
                }

                if ($mahasiswa->prodi_id != $sertifikasi->prodi_id) {
                    throw new \Exception("Mahasiswa beda prodi");
                }

                $asesmen = Asesmens::where('user_id', $mahasiswa->id)
                    ->where('sertifikasi_id', $sertifikasi->id)
                    ->first();

                if ($asesmen && !empty($asesmen->status_kompetensi)) {
                    throw new \Exception(
                        "Asesmen untuk mahasiswa {$mahasiswa->nama} sudah bersifat final dan tidak dapat diubah lagi."
                    );
                }

                $oldValues = $asesmen?->toArray();
                $certificateCode = null;

                if ($item['status_kompetensi'] === 'Kompeten') {
                    if ($asesmen && $asesmen->certificate_code) {
                        $certificateCode = $asesmen->certificate_code;
                    } else {
                        $certificateCode = CertificateService::generateCertificateCode($sertifikasi);
                    }
                }

                // 1. Ambil nama file dari DB (hanya nama file, misal: namafile.pdf)
                $fileNameOnly = $asesmen?->bukti_pendukung;

                if ($request->hasFile("asesmens.$index.bukti_pendukung")) {
                    $file = $request->file("asesmens.$index.bukti_pendukung");

                    // 2. Jika ada file lama di DB, hapus dengan menambahkan kembali path foldernya
                    if ($fileNameOnly) {
                        Storage::disk('public')->delete('bukti-sertifikasi/' . $fileNameOnly);
                    }

                    // 3. Simpan file baru ke folder 'bukti-sertifikasi'
                    $fullPath = $file->store('bukti-sertifikasi', 'public');

                    // 4. Potong path-nya menggunakan basename() agar bersisa nama filenya saja
                    $fileNameOnly = basename($fullPath);
                }

                // 5. Validasi wajib isi untuk mahasiswa kompeten
                // Karena $fileNameOnly sekarang hanya berisi nama file, pengecekan ini tetap akurat
                if (
                    $item['status_kompetensi'] === 'Kompeten' &&
                    !$request->hasFile("asesmens.$index.bukti_pendukung") &&
                    !$fileNameOnly
                ) {
                    throw new \Exception("Bukti sertifikasi wajib untuk mahasiswa kompeten");
                }

                $data = [
                    'status_kompetensi' => $item['status_kompetensi'],
                    'catatan' => $item['catatan'] ?? null,
                    'tanggal_asesmen' => $validated['tanggal_asesmen'],
                    'instruktur_id' => $instruktur->id,
                    'certificate_code' => $certificateCode ?? $asesmen?->certificate_code,
                    'bukti_pendukung' => $fileNameOnly, // 6. Simpan HANYA nama filenya saja ke DB
                ];

                if ($asesmen) {
                    $asesmen->update($data);
                } else {
                    $data['user_id'] = $mahasiswa->id;
                    $data['sertifikasi_id'] = $sertifikasi->id;
                    $asesmen = Asesmens::create($data);
                }

                AuditLog::create([
                    'user_id' => $instruktur->id,
                    'action' => $oldValues ? 'UPDATE_ASESMEN' : 'CREATE_ASESMEN',
                    'table_name' => 'asesmens',
                    'record_id' => $asesmen->id,
                    'old_values' => $oldValues ? json_encode($oldValues) : null,
                    'new_values' => json_encode($asesmen->fresh()->toArray()),
                    'ip_address' => $request->ip(),
                ]);
            }

            DB::commit();

            return [
                'code' => 200,
                'message' => 'Input asesmen berhasil'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }
}
