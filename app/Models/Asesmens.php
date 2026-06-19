<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesmens extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sertifikasi_id',
        'instruktur_id',
        'status_kompetensi',
        'bukti_pendukung',
        'catatan',
        'tanggal_asesmen',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sertifikasi()
    {
        return $this->belongsTo(Sertifikasi::class);
    }

    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
}
