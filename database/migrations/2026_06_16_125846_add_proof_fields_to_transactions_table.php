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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('payment_amount');
            $table->string('proof_image')->nullable()->after('reference_number');
            $table->string('midtrans_order_id')->nullable()->unique()->after('proof_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['reference_number', 'proof_image', 'midtrans_order_id']);
        });
    }
};
