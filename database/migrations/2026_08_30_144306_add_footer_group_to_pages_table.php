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
        Schema::table('pages', function (Blueprint $table) {
            // null = tidak tampil di footer. Menentukan kolom footer publik mana
            // yang menampilkan link ke halaman ini (lihat App\View\Components\PublicLayout).
            $table->string('footer_group', 20)->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('footer_group');
        });
    }
};
