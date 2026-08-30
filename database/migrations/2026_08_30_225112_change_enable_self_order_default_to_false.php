<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Kolom enable_self_order dibuat dengan default(true) sejak awal (migrasi
// 2026_06_17_062019) — akibatnya 7 dari 9 outlet aktif punya self-order menyala
// tanpa pernah sengaja diaktifkan admin. Migrasi ini: (1) ubah default kolom jadi
// false untuk outlet baru ke depan, (2) reset SEMUA outlet yang sudah ada ke off
// sesuai instruksi eksplisit — bukan cuma soal outlet baru.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE outlets MODIFY enable_self_order TINYINT(1) NOT NULL DEFAULT 0');

        DB::table('outlets')->update(['enable_self_order' => false]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE outlets MODIFY enable_self_order TINYINT(1) NOT NULL DEFAULT 1');

        // Sengaja TIDAK mengembalikan data outlet ke true saat rollback — tidak ada
        // catatan outlet mana yang sebelumnya benar-benar true "asli" vs cuma ikut
        // default lama, jadi rollback cuma mengembalikan default skema, bukan data.
    }
};
