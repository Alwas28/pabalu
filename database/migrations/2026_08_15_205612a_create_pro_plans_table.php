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
        Schema::create('pro_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price')->default(0);
            // null = tanpa batas. "Jenis" outlet (F&B/Retail/dst), bukan jumlah outlet.
            $table->unsignedInteger('max_outlet_types')->nullable();
            // null = tanpa batas. Jumlah akun login kasir yang boleh dibuat owner.
            $table->unsignedInteger('max_kasir')->nullable();
            $table->boolean('is_active')->default(true);
            // Paket bawaan untuk owner baru tanpa perlu kode — hanya 1 paket boleh true.
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_plans');
    }
};
