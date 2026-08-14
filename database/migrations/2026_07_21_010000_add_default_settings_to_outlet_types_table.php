<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet_types', function (Blueprint $table) {
            $table->string('default_order_mode', 20)->default('quick')->after('track_cogs');
            $table->boolean('default_enable_opening_shift')->default(false)->after('default_order_mode');
            $table->boolean('default_enable_barcode_scanner')->default(false)->after('default_enable_opening_shift');
        });
    }

    public function down(): void
    {
        Schema::table('outlet_types', function (Blueprint $table) {
            $table->dropColumn(['default_order_mode', 'default_enable_opening_shift', 'default_enable_barcode_scanner']);
        });
    }
};
