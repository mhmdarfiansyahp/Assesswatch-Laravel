<?php

namespace App\Exports;

use App\Models\Asesmens;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DashboardKompetensiExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    protected $tahun;
    protected $sertifikasiId;
    protected $prodiId;

    private $no = 1;

    public function __construct(
        $tahun = null,
        $sertifikasiId = null,
        $prodiId = null
    ) {
        $this->tahun = $tahun;
        $this->sertifikasiId = $sertifikasiId;
        $this->prodiId = $prodiId;
    }

    public function collection()
    {
        $query = Asesmens::query()
            ->join('users', 'asesmens.user_id', '=', 'users.id')
            ->join('prodi', 'users.prodi_id', '=', 'prodi.id')
            ->join('sertifikasi', 'asesmens.sertifikasi_id', '=', 'sertifikasi.id');

        if ($this->tahun) {
            $query->whereYear(
                'asesmens.tanggal_asesmen',
                $this->tahun
            );
        }

        if ($this->sertifikasiId) {
            $query->where(
                'asesmens.sertifikasi_id',
                $this->sertifikasiId
            );
        }

        if ($this->prodiId) {
            $query->where(
                'users.prodi_id',
                $this->prodiId
            );
        }

        return $query
            ->select(
                'prodi.nama_prodi',

                DB::raw("
                    SUM(
                        CASE
                        WHEN asesmens.status_kompetensi = 'Kompeten'
                        THEN 1 ELSE 0
                        END
                    ) as kompeten
                "),

                DB::raw("
                    SUM(
                        CASE
                        WHEN asesmens.status_kompetensi = 'Tidak Kompeten'
                        THEN 1 ELSE 0
                        END
                    ) as tidak_kompeten
                "),

                DB::raw("
                    SUM(
                        CASE
                        WHEN asesmens.status_kompetensi = 'Tidak Hadir'
                        THEN 1 ELSE 0
                        END
                    ) as tidak_hadir
                ")
            )
            ->groupBy(
                'prodi.id',
                'prodi.nama_prodi'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Program Studi',
            'Kompeten',
            'Tidak Kompeten',
            'Tidak Hadir',
        ];
    }

    public function map($row): array
    {
        return [
            $this->no++,
            $row->nama_prodi,
            $row->kompeten,
            $row->tidak_kompeten,
            $row->tidak_hadir,
        ];
    }
}