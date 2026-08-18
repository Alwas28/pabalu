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
        Schema::table('pro_invoice_payment_codes', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('max_uses');
            // Kode "gratis": tagihan yang dilunasi lewat kode ini dihitung sebagai
            // digratiskan (bukan pendapatan), beda dengan kode biasa yang berarti
            // konfirmasi pembayaran sungguhan (mis. transfer manual).
            $table->boolean('is_free')->default(false)->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_invoice_payment_codes', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'is_free']);
        });
    }
};
