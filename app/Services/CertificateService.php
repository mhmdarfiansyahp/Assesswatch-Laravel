<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Asesmens;
use App\Models\Sertifikasi;

class CertificateService
{
    public static function generateCertificateCode(Sertifikasi $sertifikasi)
    {
        do {
            $ulid = substr((string) Str::ulid(), -10);
            $code = strtoupper($sertifikasi->scheme_code) . '-' . strtoupper($ulid);
        } while (
            Asesmens::where('certificate_code', $code)->exists()
        );

        return $code;
    }
}