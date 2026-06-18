<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_in_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in_items');
    }
};
