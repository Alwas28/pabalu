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
        Schema::create('pro_invoice_payment_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            // Berapa outlet BERBEDA boleh pakai kode yang sama untuk melunasi tagihan mereka.
            $table->unsignedInteger('max_uses');
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_invoice_payment_codes');
    }
};
