<?php

use App\Http\Controllers\Api\AsesmenController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProdiController;
use App\Http\Controllers\API\SertifikasiController;
use App\Http\Controllers\API\UserController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('prodi', ProdiController::class);
        Route::apiResource('sertifikasi', SertifikasiController::class);
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
});
