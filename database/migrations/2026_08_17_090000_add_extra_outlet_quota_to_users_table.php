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
        Schema::table('users', function (Blueprint $table) {
            // Tambahan kuota outlet KHUSUS owner ini, di luar batas paket Pro-nya (mis.
            // paket cuma boleh 2 outlet, tapi admin izinkan owner tertentu +1 jadi 3).
            // Ditambahkan ke max_outlet_types paket saat menghitung batas efektif — tidak
            // berlaku kalau paketnya sudah "tanpa batas" (null).
            $table->unsignedInteger('extra_outlet_quota')->default(0)->after('setup_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('extra_outlet_quota');
        });
    }
};
