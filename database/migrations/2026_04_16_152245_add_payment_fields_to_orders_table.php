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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_token')->nullable()->after('order_status');
            $table->timestamp('paid_at')->nullable()->after('completed_at');
        });

        // Tambah pending_payment ke enum
        \DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending_payment','pending','processing','ready','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'paid_at']);
        });

        \DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending','processing','ready','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
