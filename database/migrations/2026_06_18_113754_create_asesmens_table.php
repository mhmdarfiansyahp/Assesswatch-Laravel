<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asesmens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('sertifikasi_id')->unsigned();
            $table->foreign('sertifikasi_id')->references('id')->on('sertifikasi');
            $table->bigInteger('instruktur_id')->unsigned();
            $table->foreign('instruktur_id')->references('id')->on('users');
            $table->enum('status_kompetensi', ['Kompeten', 'Tidak Kompeten', 'Tidak Hadir'])->nullable();
            $table->string('bukti_pendukung', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal_asesmen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmens');
    }
};
