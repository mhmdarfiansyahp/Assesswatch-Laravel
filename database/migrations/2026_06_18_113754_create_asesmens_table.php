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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sertifikasi_id')->constrained('sertifikasi')->cascadeOnDelete();
            $table->foreignId('instruktur_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_kompetensi', ['Kompeten', 'Tidak Kompeten', 'Tidak Hadir'])->nullable();
            $table->string('bukti_pendukung', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal_asesmen')->nullable();
            $table->string('certificate_code')->nullable()->unique();
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
