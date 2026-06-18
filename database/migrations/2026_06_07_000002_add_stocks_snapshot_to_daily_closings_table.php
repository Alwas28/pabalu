<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->text('stocks_snapshot')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropColumn('stocks_snapshot');
        });
    }
};
