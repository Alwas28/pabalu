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
        Schema::table('pro_plans', function (Blueprint $table) {
            // Paket yang owner bisa aktifkan sendiri dari halaman /langganan TANPA kode
            // aktivasi (mis. paket usage-based yang ditagih belakangan lewat Tagihan Outlet,
            // bukan bayar di muka). Paket berbayar biasa (Pro/Max) tetap butuh kode.
            $table->boolean('is_self_activatable')->default(false)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_plans', function (Blueprint $table) {
            $table->dropColumn('is_self_activatable');
        });
    }
};
