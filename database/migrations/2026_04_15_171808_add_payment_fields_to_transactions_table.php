<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('metode_bayar', ['tunai', 'qris', 'transfer'])->default('tunai')->after('status');
            $table->string('bukti_bayar')->nullable()->after('metode_bayar');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['metode_bayar', 'bukti_bayar']);
        });
    }
};
