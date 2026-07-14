<?php

use App\Http\Controllers\Api\AsesmenController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\MahasiswaKompetensiController;
use App\Http\Controllers\API\ProdiController;
use App\Http\Controllers\API\SertifikasiController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::get('/verifikasi/sertifikat/{code}', [MahasiswaKompetensiController::class, 'verifikasi'])
    ->name('verifikasi.sertifikat');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    Route::middleware('role:admin|instruktur')->group(function () {

        Route::get(
            '/dashboard/kompetensi-prodi',
            [DashboardController::class, 'kompetensiPerProdi']
        );

        Route::get(
            '/dashboard/export-excel',
            [DashboardController::class, 'exportExcel']
        );

        Route::get(
            '/dashboard/export-pdf',
            [DashboardController::class, 'exportPdf']
        );

        Route::get(
            '/sertifikasi',
            [SertifikasiController::class, 'index']
        );

        Route::get(
            '/prodi',
            [ProdiController::class, 'index']
        );
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('prodi', ProdiController::class)
            ->except(['index']);

        Route::apiResource('sertifikasi', SertifikasiController::class)
            ->except(['index']);
    });

    Route::middleware('role:instruktur')->group(function () {
        Route::get(
            '/asesmen/sertifikasi/{sertifikasi}',
            [AsesmenController::class, 'listMahasiswa']
        );

        Route::post(
            '/asesmen/bulk-input',
            [AsesmenController::class, 'bulkInput']
        );
        Route::get(
            '/instruktur/sertifikasi',
            [SertifikasiController::class, 'getSertifikasi']
        );
    });

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get(
            '/mahasiswa/kompetensi',
            [MahasiswaKompetensiController::class, 'index']
        );

        Route::get(
            '/mahasiswa/kompetensi/{asesmen}/download',
            [MahasiswaKompetensiController::class, 'download']
        );
    });
});
