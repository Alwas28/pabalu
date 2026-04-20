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
            if (!Schema::hasColumn('users', 'outlet_id')) {
                $table->foreignId('outlet_id')
                      ->nullable()
                      ->after('remember_token')
                      ->constrained('outlets')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Outlet::class);
            $table->dropColumn('outlet_id');
        });
    }
};
