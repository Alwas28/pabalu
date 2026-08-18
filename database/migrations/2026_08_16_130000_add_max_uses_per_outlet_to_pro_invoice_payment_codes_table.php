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
        Schema::table('pro_invoice_payment_codes', function (Blueprint $table) {
            // Batas pemakaian oleh SATU outlet yang sama (mis. bayar beberapa tagihan
            // bulanan sekaligus dengan 1 kode) — terpisah dari max_uses yang membatasi
            // total pemakaian gabungan SEMUA outlet. null = tidak ada batas per-outlet,
            // hanya dibatasi max_uses global.
            $table->unsignedInteger('max_uses_per_outlet')->nullable()->after('max_uses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_invoice_payment_codes', function (Blueprint $table) {
            $table->dropColumn('max_uses_per_outlet');
        });
    }
};
