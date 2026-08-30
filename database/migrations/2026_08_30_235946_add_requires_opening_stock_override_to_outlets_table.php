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
            // null = ikut default Jenis Outlet (OutletType::requires_opening_stock),
            // perilaku SEMUA outlet lama tidak berubah. true/false = override eksplisit
            // per outlet oleh owner lewat Pengaturan (lihat Outlet::requiresOpeningStock()).
            $table->boolean('requires_opening_stock')->nullable()->after('enable_barcode_scanner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('requires_opening_stock');
        });
    }
};
