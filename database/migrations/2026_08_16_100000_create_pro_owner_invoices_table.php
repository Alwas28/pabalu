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
        Schema::create('pro_owner_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->enum('period_type', ['bulan', 'tahun']);
            $table->date('period_start');
            $table->date('period_end');
            // Total transaksi (omset) outlet pada periode ini, dihitung otomatis dari data transaksi
            // sebagai acuan admin — BUKAN nominal tagihan. Nominal tagihan tetap diinput manual karena
            // tarif per rentang omset berbeda-beda dan bisa berubah sewaktu-waktu.
            $table->unsignedBigInteger('transaction_total')->default(0);
            $table->unsignedInteger('amount');
            $table->text('note')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['outlet_id', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_owner_invoices');
    }
};
