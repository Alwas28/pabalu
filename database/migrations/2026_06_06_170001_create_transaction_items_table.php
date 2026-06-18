<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 150);     // snapshot saat transaksi
            $table->unsignedBigInteger('product_price'); // snapshot harga saat transaksi
            $table->unsignedSmallInteger('qty');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();

            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
