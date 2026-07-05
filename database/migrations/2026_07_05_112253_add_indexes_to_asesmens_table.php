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
        Schema::table('asesmens', function (Blueprint $table) {

            $table->index(
                ['sertifikasi_id', 'user_id'],
                'idx_asesmens_sertifikasi_user'
            );

            $table->index(
                ['sertifikasi_id', 'user_id', 'status_kompetensi'],
                'idx_asesmens_sertifikasi_user_status'
            );
        });

        Schema::table('users', function (Blueprint $table) {

            $table->index(
                ['role', 'prodi_id'],
                'idx_users_role_prodi'
            );
        });

        Schema::table('instruktur_prodi', function (Blueprint $table) {

            $table->index(
                ['instruktur_id', 'prodi_id'],
                'idx_instruktur_prodi'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmens', function (Blueprint $table) {

            $table->dropIndex('idx_asesmens_sertifikasi_user');

            $table->dropIndex('idx_asesmens_sertifikasi_user_status');
        });

        Schema::table('users', function (Blueprint $table) {

            $table->dropIndex('idx_users_role_prodi');
        });

        Schema::table('instruktur_prodi', function (Blueprint $table) {

            $table->dropIndex('idx_instruktur_prodi');
        });
    }
};
