<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_token', 40)->nullable()->unique()->after('order_number');
        });

        Schema::table('laundry_orders', function (Blueprint $table) {
            $table->string('tracking_token', 40)->nullable()->unique()->after('order_number');
        });

        // Backfill token untuk order lama supaya tracking link (yang akan diganti
        // ke berbasis token) tetap bisa dipakai untuk data yang sudah ada.
        DB::table('orders')->whereNull('tracking_token')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('orders')->where('id', $row->id)->update(['tracking_token' => Str::random(40)]);
            }
        });
        DB::table('laundry_orders')->whereNull('tracking_token')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('laundry_orders')->where('id', $row->id)->update(['tracking_token' => Str::random(40)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });

        Schema::table('laundry_orders', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });
    }
};
