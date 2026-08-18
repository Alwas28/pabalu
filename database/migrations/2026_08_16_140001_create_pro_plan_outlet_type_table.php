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
        // Jenis outlet yang boleh dibuat owner di bawah paket ini. Kosong (tidak ada baris
        // untuk paket tsb) = tidak dibatasi, semua jenis outlet boleh — supaya paket lama
        // (Free/Pro/Max) yang belum pernah diatur tetap berperilaku sama seperti sekarang.
        Schema::create('pro_plan_outlet_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_plan_id')->constrained('pro_plans')->cascadeOnDelete();
            $table->foreignId('outlet_type_id')->constrained('outlet_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pro_plan_id', 'outlet_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_plan_outlet_type');
    }
};
