<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sertifikasi extends Model
{
    use HasFactory;
    protected $table = 'sertifikasi';

    protected $fillable = [
        'prodi_id',
        'nama_sertifikasi',
        'lembaga',
        'level',
        'tanggal_sertifikasi',
        'scheme_code',
        'status',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function asesmens()
    {
        return $this->hasMany(Asesmens::class);
    }

    public function getNamaProdi($id_sertifikasi)
    {
        $nama_prodi = Sertifikasi::find($id_sertifikasi)->prodi->nama_prodi;

        return response()->json($nama_prodi);
    }
}
