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
        Schema::table('users', function (Blueprint $table) {
            // Kode OTP disimpan ter-hash (bukan plaintext), mirip pola password —
            // dibandingkan pakai Hash::check() di WhatsAppVerificationService.
            $table->string('wa_verification_code', 100)->nullable()->after('setup_completed_at');
            $table->timestamp('wa_verification_expires_at')->nullable()->after('wa_verification_code');
            $table->unsignedTinyInteger('wa_verification_attempts')->default(0)->after('wa_verification_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wa_verification_code', 'wa_verification_expires_at', 'wa_verification_attempts']);
        });
    }
};
