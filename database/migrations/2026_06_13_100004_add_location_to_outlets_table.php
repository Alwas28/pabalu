<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete()->after('bidang_usaha');
            $table->foreignId('regency_id')->nullable()->constrained('regencies')->nullOnDelete()->after('province_id');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete()->after('regency_id');
            $table->string('kelurahan', 100)->nullable()->after('district_id');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['province_id', 'regency_id', 'district_id', 'kelurahan']);
        });
    }
};
