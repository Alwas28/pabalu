<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('sku', 60)->nullable();
            $table->string('unit', 30)->default('pcs');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('cost_price')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['outlet_id', 'category_id']);
            $table->index(['outlet_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
