<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('kategori', 100);
            $table->text('keterangan')->nullable();
            $table->decimal('jumlah', 12, 2);
            $table->timestamps();

            $table->index(['outlet_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
