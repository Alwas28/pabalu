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
        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('returned_at');
            $table->unsignedInteger('refund_amount')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->dropColumn(['refunded_at', 'refund_amount']);
        });
    }
};
