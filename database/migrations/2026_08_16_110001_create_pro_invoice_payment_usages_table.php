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
        Schema::create('pro_invoice_payment_usages', function (Blueprint $table) {
            $table->id();
            // nullOnDelete: kalau kode dihapus admin, riwayat pelunasan outlet TETAP ada
            // (sama seperti pro_owner_subscriptions.pro_redemption_code_id) — tagihan yang
            // sudah lunas tidak boleh "batal lunas" hanya karena kodenya dihapus.
            $table->foreignId('pro_invoice_payment_code_id')->nullable()
                ->constrained('pro_invoice_payment_codes')->nullOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('pro_owner_invoice_id')->constrained('pro_owner_invoices')->cascadeOnDelete();
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('used_at');
            $table->timestamps();

            $table->index(['pro_invoice_payment_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_invoice_payment_usages');
    }
};
