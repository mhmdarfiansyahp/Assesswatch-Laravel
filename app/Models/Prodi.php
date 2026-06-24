<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodi';
    protected $fillable = [
        'nama_prodi',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sertifikasis()
    {
        return $this->hasMany(Sertifikasi::class);
    }

    public function instrukturs()
    {
        return $this->belongsToMany(
            User::class,
            'instruktur_prodi',
            'prodi_id',
            'instruktur_id'
        );
    }
}
