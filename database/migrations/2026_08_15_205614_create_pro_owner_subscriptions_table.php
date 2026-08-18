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
        Schema::create('pro_owner_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pro_plan_id')->constrained('pro_plans')->restrictOnDelete();
            // Kode yang dipakai untuk mengaktifkan baris ini — null kalau ini paket
            // bawaan (Free) yang otomatis diberikan tanpa kode.
            $table->foreignId('pro_redemption_code_id')->nullable()
                ->constrained('pro_redemption_codes')->nullOnDelete();
            $table->dateTime('activated_at');
            // null = tidak pernah kadaluarsa (mis. paket Free bawaan).
            $table->dateTime('expires_at')->nullable();
            // Kapan reminder H-7 terakhir dikirim — mencegah cron mengirim berkali-kali.
            $table->dateTime('reminder_sent_at')->nullable();
            $table->timestamps();

            // Baris TERBARU (activated_at paling akhir) per owner = langganan yang sedang aktif.
            $table->index(['owner_id', 'activated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_owner_subscriptions');
    }
};
