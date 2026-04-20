<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN metode_bayar ENUM('tunai','qris','transfer','gateway') NOT NULL DEFAULT 'tunai'");

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'payment_ref')) {
                $table->string('payment_ref', 100)->nullable()->after('bukti_bayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_ref');
        });
        DB::statement("ALTER TABLE transactions MODIFY COLUMN metode_bayar ENUM('tunai','qris','transfer') NOT NULL DEFAULT 'tunai'");
    }
};
