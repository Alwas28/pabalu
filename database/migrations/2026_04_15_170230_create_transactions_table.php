<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_transaksi', 30)->unique();
            $table->date('tanggal');
            $table->decimal('total', 12, 2);
            $table->decimal('bayar', 12, 2);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['paid', 'void'])->default('paid');
            $table->timestamps();

            $table->index(['outlet_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
