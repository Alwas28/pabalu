<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_menus', function (Blueprint $table) {
            // 'simple' = dropdown biasa (seperti Home), 'mega' = grid mega menu (seperti Belanja).
            // Kolom ini hanya relevan untuk menu utama (parent_id null).
            $table->string('type', 10)->default('simple')->after('label');
            // Judul kolom pengelompokan di dalam mega menu. Hanya relevan untuk sub-menu
            // yang induknya bertipe 'mega' — sub-menu dengan group_label sama tampil satu kolom.
            $table->string('group_label', 100)->nullable()->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('home_menus', function (Blueprint $table) {
            $table->dropColumn(['type', 'group_label']);
        });
    }
};
