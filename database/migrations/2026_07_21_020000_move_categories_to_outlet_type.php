<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori pindah dari milik outlet (diisi owner) → milik jenis outlet (dikelola admin).
        // Kategori lama per-outlet tidak bisa dipetakan otomatis ke daftar admin yang baru,
        // jadi dihapus di sini — produk yang memakainya otomatis jadi tanpa kategori
        // (FK category_id sudah nullOnDelete) dan perlu diatur ulang manual oleh admin/owner.
        DB::table('categories')->delete();

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropColumn('outlet_id');
            $table->foreignId('outlet_type_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['outlet_type_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['outlet_type_id']);
            $table->dropIndex(['outlet_type_id', 'sort_order']);
            $table->dropColumn('outlet_type_id');
            $table->foreignId('outlet_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['outlet_id', 'sort_order']);
        });
    }
};
