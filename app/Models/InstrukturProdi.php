<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrukturProdi extends Model
{
    protected $table = 'instruktur_prodi';

    protected $fillable = [
        'instruktur_id',
        'prodi_id',
    ];

    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}