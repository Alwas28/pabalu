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
        Schema::table('outlets', function (Blueprint $table) {
            $table->boolean('enable_qris_transfer')->default(false)->after('enable_barcode_scanner');
            $table->boolean('enable_qris_pay')->default(false)->after('enable_qris_transfer');
            $table->boolean('enable_transfer')->default(false)->after('enable_qris_pay');
            $table->boolean('enable_card')->default(false)->after('enable_transfer');
            $table->string('midtrans_server_key')->nullable()->after('enable_card');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_server_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_client_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn([
                'enable_qris_transfer',
                'enable_qris_pay',
                'enable_transfer',
                'enable_card',
                'midtrans_server_key',
                'midtrans_client_key',
                'midtrans_is_production',
            ]);
        });
    }
};
