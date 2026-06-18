<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('old_price');
            $table->unsignedBigInteger('new_price');
            $table->integer('stock_at_change')->default(0); // stok saat harga diubah
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
    }
};
