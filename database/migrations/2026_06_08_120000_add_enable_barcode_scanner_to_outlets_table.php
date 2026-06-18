<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->boolean('enable_barcode_scanner')->default(false)->after('enable_opening_shift');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('enable_barcode_scanner');
        });
    }
};
